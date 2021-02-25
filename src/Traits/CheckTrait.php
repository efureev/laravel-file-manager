<?php

declare(strict_types=1);

namespace Alexusmai\LaravelFileManager\Traits;

use Illuminate\Support\Facades\Storage;

trait CheckTrait
{
    /**
     * Check disk name
     *
     * @param string $name
     *
     * @return bool
     */
    public function checkDisk(string $name): bool
    {
        return in_array($name, $this->configRepository->getDiskList(), true)
            && array_key_exists($name, config('filesystems.disks'));
    }

    /**
     * Check Disk and Path
     *
     * @param string $disk
     * @param string $path
     *
     * @return bool
     */
    public function checkPath(string $disk, string $path): bool
    {
        // check disk name
        if (!$this->checkDisk($disk)) {
            return false;
        }

        // check path
        if ($path && !Storage::disk($disk)->exists($path)) {
            return false;
        }

        return true;
    }
}
