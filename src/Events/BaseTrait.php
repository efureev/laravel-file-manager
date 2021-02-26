<?php

declare(strict_types=1);

namespace Alexusmai\LaravelFileManager\Events;

trait BaseTrait
{
    use DiskTrait;

    private string $path;


    /**
     * @return string
     */
    public function path(): string
    {
        return $this->path;
    }
}
