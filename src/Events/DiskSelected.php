<?php

declare(strict_types=1);

namespace Alexusmai\LaravelFileManager\Events;

final class DiskSelected
{
    use DiskTrait;

    /**
     * DiskSelected constructor.
     *
     * @param $disk
     */
    public function __construct(string $disk)
    {
        $this->disk = $disk;
    }
}
