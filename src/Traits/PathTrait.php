<?php

declare(strict_types=1);

namespace Alexusmai\LaravelFileManager\Traits;

trait PathTrait
{
    /**
     * Create path for new directory / file
     *
     * @param $path
     * @param $name
     *
     * @return string
     */
    public static function newPath(string $path, string $name): string
    {
        if (!$path) {
            return $name;
        }

        return "{$path}/{$name}";
    }

    /**
     * Rename path - for copy / cut operations
     *
     * @param string $itemPath
     * @param string|null $recipientPath
     *
     * @return string
     */
    public static function renamePath(string $itemPath, string $recipientPath = null): string
    {
        if ($recipientPath) {
            return $recipientPath . '/' . basename($itemPath);
        }

        return basename($itemPath);
    }

    /**
     * Transform path name
     *
     * @param string $itemPath
     * @param string $recipientPath
     * @param int $partsForRemove
     *
     * @return string
     */
    public static function transformPath(string $itemPath, string $recipientPath, int $partsForRemove): string
    {
        $elements = array_slice(explode('/', $itemPath), $partsForRemove);

        if ($recipientPath) {
            return $recipientPath . '/' . implode('/', $elements);
        }

        return implode('/', $elements);
    }
}
