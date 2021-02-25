<?php

declare(strict_types=1);

namespace Alexusmai\LaravelFileManager\Services;

use Alexusmai\LaravelFileManager\Events\UnzipCreated;
use Alexusmai\LaravelFileManager\Events\UnzipFailed;
use Alexusmai\LaravelFileManager\Events\ZipCreated;
use Alexusmai\LaravelFileManager\Events\ZipFailed;
use Alexusmai\LaravelFileManager\Exceptions\UnZipException;
use Alexusmai\LaravelFileManager\Exceptions\ZipException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class Zip
{
    protected ZipArchive $zip;
    protected Request $request;
    protected string $pathPrefix;

    /**
     * Zip constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->zip        = new ZipArchive();
        $this->request    = $request;
        $this->pathPrefix = Storage::disk($request->input('disk'))
            ->getDriver()
            ->getAdapter()
            ->getPathPrefix();
    }

    /**
     * Create new zip archive
     * @throw ZipException
     */
    public function create(): void
    {
        $this->createArchive();
    }

    /**
     * Extract
     *
     * @throw UnZipException
     */
    public function extract(): void
    {
        $this->extractArchive();
    }

    /**
     * Create zip archive
     */
    protected function createArchive(): void
    {
        // elements list
        $elements = $this->request->input('elements');

        // create or overwrite archive
        if ($this->zip->open($this->createName(), (ZIPARCHIVE::OVERWRITE | ZIPARCHIVE::CREATE)) === true) {
            // files processing
            if ($elements['files']) {
                foreach ($elements['files'] as $file) {
                    $this->zip->addFile(
                        $this->pathPrefix . $file,
                        basename($file)
                    );
                }
            }

            // directories processing
            if ($elements['directories']) {
                $this->addDirs($elements['directories']);
            }

            $this->zip->close();

            event(new ZipCreated($this->request));

            return;
        }

        event(new ZipFailed($this->request));

        ZipException::throw();
    }

    /**
     * Archive extract
     *
     * @return void
     * @throw UnZipException
     */
    protected function extractArchive(): void
    {
        $zipPath = $this->pathPrefix . $this->request->input('path');

        $rootPath = dirname($zipPath);

        // extract to new folder
        $folder = $this->request->input('folder');

        if ($this->zip->open($zipPath) === true) {
            $this->zip->extractTo($folder ? $rootPath . '/' . $folder : $rootPath);
            $this->zip->close();

            event(new UnzipCreated($this->request));

            return;
        }

        event(new UnzipFailed($this->request));

        UnZipException::throw();
    }

    /**
     * Add directories - recursive
     *
     * @param array $directories
     */
    protected function addDirs(array $directories): void
    {
        foreach ($directories as $directory) {
            // Create recursive directory iterator
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($this->pathPrefix . $directory),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $name => $file) {
                // Get real and relative path for current item
                $filePath     = $file->getRealPath();
                $relativePath = substr(
                    $filePath,
                    strlen($this->fullPath($this->request->input('path')))
                );

                if (!$file->isDir()) {
                    // Add current file to archive
                    $this->zip->addFile($filePath, $relativePath);
                } else {
                    // add empty folders
                    if (!glob($filePath . '/*')) {
                        $this->zip->addEmptyDir($relativePath);
                    }
                }
            }
        }
    }

    /**
     * Create archive name with full path
     *
     * @return string
     */
    protected function createName(): string
    {
        return $this->fullPath($this->request->input('path'))
            . $this->request->input('name');
    }

    /**
     * Generate full path
     *
     * @param $path
     *
     * @return string
     */
    protected function fullPath($path): string
    {
        return $path ? $this->pathPrefix . $path . '/' : $this->pathPrefix;
    }
}
