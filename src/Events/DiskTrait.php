<?php

declare(strict_types=1);

namespace Alexusmai\LaravelFileManager\Events;

trait DiskTrait
{
    private string $disk;

    /**
     * @return string
     */
    public function disk(): string
    {
        return $this->disk;
    }
}
