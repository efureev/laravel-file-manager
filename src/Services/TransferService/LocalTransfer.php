<?php

namespace Alexusmai\LaravelFileManager\Services\TransferService;

use Alexusmai\LaravelFileManager\Traits\PathTrait;
use Illuminate\Support\Facades\Storage;

class LocalTransfer extends Transfer
{
    use PathTrait;

    /**
     * Copy files and folders
     */
    protected function copy(): void
    {
        // files
        foreach ($this->clipboard['files'] as $file) {
            $path = static::renamePath($file, $this->path);
            $path = static::buildPathName($this->disk, $path);

            Storage::disk($this->disk)->copy($file, $path);
        }

        // directories
        foreach ($this->clipboard['directories'] as $directory) {
            $this->copyDirectory($directory);
        }
    }

    /**
     * Cut files and folders
     */
    protected function cut(): void
    {
        // files
        foreach ($this->clipboard['files'] as $file) {
            Storage::disk($this->disk)->move(
                $file,
                static::renamePath($file, $this->path)
            );
        }

        // directories
        foreach ($this->clipboard['directories'] as $directory) {
            Storage::disk($this->disk)->move(
                $directory,
                static::renamePath($directory, $this->path)
            );
        }
    }

    /**
     * Copy directory
     *
     * @param string $directory
     */
    protected function copyDirectory(string $directory): void
    {
        // get all directories in this directory
        $allDirectories = Storage::disk($this->disk)
            ->allDirectories($directory);

        $partsForRemove = (count(explode('/', $directory)) - 1);

        // create this directories
        foreach ($allDirectories as $dir) {
            Storage::disk($this->disk)->makeDirectory(
                static::transformPath(
                    $dir,
                    $this->path,
                    $partsForRemove
                )
            );
        }

        // get all files
        $allFiles = Storage::disk($this->disk)->allFiles($directory);

        // copy files
        foreach ($allFiles as $file) {
            Storage::disk($this->disk)->copy(
                $file,
                static::transformPath($file, $this->path, $partsForRemove)
            );
        }
    }
}
