<?php

declare(strict_types=1);

namespace Alexusmai\LaravelFileManager\Traits;

use Alexusmai\LaravelFileManager\Services\ACLService\ACL;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

trait ContentTrait
{

    /**
     * Get content for the selected disk and path
     *
     * @param string $disk
     * @param string $path
     *
     * @return array
     */
    public function getContent(string $disk, string $path): array
    {
        $content = Storage::disk($disk)->listContents($path);

        // get a list of directories
        $directories = $this->filterDir($disk, $content);

        // get a list of files
        $files = $this->filterFile($disk, $content);

        return compact('directories', 'files');
    }

    /**
     * Get directories with properties
     *
     * @param string $disk
     * @param null $path
     *
     * @return array
     */
    public function directoriesWithProperties(string $disk, $path = null): array
    {
        $content = Storage::disk($disk)->listContents($path);

        return $this->filterDir($disk, $content);
    }

    /**
     * Get files with properties
     *
     * @param string $disk
     * @param null $path
     *
     * @return array
     */
    public function filesWithProperties(string $disk, $path = null): array
    {
        $content = Storage::disk($disk)->listContents($path);

        return $this->filterFile($disk, $content);
    }

    /**
     * Get directories for tree module
     *
     * @param string $disk
     * @param string $path
     *
     * @return array
     */
    public function getDirectoriesTree(string $disk, string $path): array
    {
        $directories = $this->directoriesWithProperties($disk, $path);

        foreach ($directories as $index => $dir) {
            $directories[$index]['props'] = [
                'hasSubdirectories' => Storage::disk($disk)
                    ->directories($dir['path']) ? true : false,
            ];
        }

        return $directories;
    }

    /**
     * File properties
     *
     * @param string $disk
     * @param string|null $path
     *
     * @return array
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function fileProperties(string $disk, string $path = null): array
    {
        $file = Storage::disk($disk)->getMetadata($path);

        $pathInfo = pathinfo($path);

        $file['basename']  = $pathInfo['basename'];
        $file['dirname']   = $pathInfo['dirname'] === '.' ? '' : $pathInfo['dirname'];
        $file['extension'] = $pathInfo['extension'] ?? '';
        $file['filename']  = $pathInfo['filename'];

        // if ACL ON
        if ($this->configRepository->getAcl()) {
            return $this->aclFilter($disk, [$file])[0];
        }

        return $file;
    }

    /**
     * Get properties for the selected directory
     *
     * @param string $disk
     * @param string|null $path
     *
     * @return array
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function directoryProperties(string $disk, string $path = null): array
    {
        $directory = Storage::disk($disk)->getMetadata($path);

        $pathInfo = pathinfo($path);

        /**
         * S3 didn't return metadata for directories
         */
        if (!$directory) {
            $directory['path'] = $path;
            $directory['type'] = 'dir';
        }

        $directory['basename'] = $pathInfo['basename'];
        $directory['dirname']  = $pathInfo['dirname'] === '.' ? '' : $pathInfo['dirname'];

        // if ACL ON
        if ($this->configRepository->getAcl()) {
            return $this->aclFilter($disk, [$directory])[0];
        }

        return $directory;
    }

    /**
     * Get only directories
     *
     * @param string $disk
     * @param $content
     *
     * @return array
     */
    protected function filterDir(string $disk, $content): array
    {
        // select only dir
        $dirsList = Arr::where($content, fn($item) => $item['type'] === 'dir');

        // remove 'filename' param
        $dirs = array_map(
            static fn($item) => Arr::except($item, ['filename']),
            $dirsList
        );

        // if ACL ON
        if ($this->configRepository->getAcl()) {
            return array_values($this->aclFilter($disk, $dirs));
        }

        return array_values($dirs);
    }

    /**
     * Get only files
     *
     * @param $disk
     * @param $content
     *
     * @return array
     */
    protected function filterFile($disk, $content): array
    {
        // select only files
        $files = Arr::where(
            $content,
            function ($item) {
                return $item['type'] === 'file';
            }
        );

        // if ACL ON
        if ($this->configRepository->getAcl()) {
            return array_values($this->aclFilter($disk, $files));
        }

        return array_values($files);
    }

    /**
     * ACL filter
     *
     * @param $disk
     * @param $content
     *
     * @return array
     */
    protected function aclFilter($disk, $content): array
    {
        $acl = resolve(ACL::class);

        $withAccess = array_map(
            static function ($item) use ($acl, $disk) {
                // add acl access level
                $item['acl'] = $acl->getAccessLevel($disk, $item['path']);

                return $item;
            },
            $content
        );

        // filter files and folders
        if ($this->configRepository->getAclHideFromFM()) {
            return array_filter(
                $withAccess,
                static fn($item) => $item['acl'] !== 0
            );
        }

        return $withAccess;
    }
}
