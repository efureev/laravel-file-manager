<?php

declare(strict_types=1);

namespace Alexusmai\LaravelFileManager\Events;

class Deleting
{
    use DiskTrait;

    private array $items;

    /**
     * Deleted constructor.
     *
     * Deleting constructor.
     *
     * @param string $disk
     * @param array $items
     */
    public function __construct(string $disk, array $items)
    {
        $this->disk  = $disk;
        $this->items = $items;
    }

    /**
     * @return array
     */
    public function items(): array
    {
        return $this->items;
    }
}
