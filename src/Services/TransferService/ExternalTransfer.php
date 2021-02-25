<?php

namespace Alexusmai\LaravelFileManager\Services\TransferService;

use Alexusmai\LaravelFileManager\Traits\PathTrait;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\MountManager;

class ExternalTransfer extends Transfer
{
    use PathTrait;

    public MountManager $manager;

    /**
     * ExternalTransfer constructor.
     *
     * @param string $disk
     * @param string $path
     * @param array $clipboard
     */
    public function __construct(string $disk, string $path, array $clipboard)
    {
        parent::__construct($disk, $path, $clipboard);

        $this->manager = new MountManager(
            [
                'from' => Storage::drive($clipboard['disk'])->getDriver(),
                'to'   => Storage::drive($disk)->getDriver(),
            ]
        );
    }

    /**
     * Copy files and folders
     *
     * @throws \League\Flysystem\FileExistsException
     */
    protected function copy(): void
    {
        // files
        foreach ($this->clipboard['files'] as $file) {
            $this->copyToDisk(
                $file,
                static::renamePath($file, $this->path)
            );
        }

        // directories
        foreach ($this->clipboard['directories'] as $directory) {
            $this->copyDirectoryToDisk($directory);
        }
    }

    /**
     * Cut files and folders
     *
     * @throws \League\Flysystem\FileExistsException
     */
    protected function cut(): void
    {
        // files
        foreach ($this->clipboard['files'] as $file) {
            $this->moveToDisk(
                $file,
                static::renamePath($file, $this->path)
            );
        }

        // directories
        foreach ($this->clipboard['directories'] as $directory) {
            $this->copyDirectoryToDisk($directory);

            // remove directory
            Storage::disk($this->clipboard['disk'])
                ->deleteDirectory($directory);
        }
    }

    /**
     * Copy directory to another disk
     *
     * @param $directory
     *
     * @throws \League\Flysystem\FileExistsException
     */
    protected function copyDirectoryToDisk($directory): void
    {
        // get all directories in this directory
        $allDirectories = Storage::disk($this->clipboard['disk'])
            ->allDirectories($directory);

        $partsForRemove = (count(explode('/', $directory)) - 1);

        // create this directories
        foreach ($allDirectories as $dir) {
            Storage::disk($this->disk)->makeDirectory(
                static::transformPath($dir, $this->path, $partsForRemove)
            );
        }

        // get all files
        $allFiles = Storage::disk($this->clipboard['disk'])
            ->allFiles($directory);

        // copy files
        foreach ($allFiles as $file) {
            $this->copyToDisk(
                $file,
                static::transformPath($file, $this->path, $partsForRemove)
            );
        }
    }

    /**
     * Copy files to disk
     *
     * @param $filePath
     * @param $newPath
     *
     * @throws \League\Flysystem\FileExistsException
     */
    protected function copyToDisk($filePath, $newPath): void
    {
        $this->manager->copy(
            'from://' . $filePath,
            'to://' . $newPath
        );
    }

    /**
     * Move files to disk
     *
     * @param $filePath
     * @param $newPath
     */
    protected function moveToDisk($filePath, $newPath): void
    {
        $this->manager->move(
            'from://' . $filePath,
            'to://' . $newPath
        );
    }
}
