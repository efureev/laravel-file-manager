<?php

namespace Alexusmai\LaravelFileManager\Services\TransferService;

class TransferFactory
{
    /**
     * @param string $disk
     * @param string $path
     * @param array $clipboard
     *
     * @return ExternalTransfer|LocalTransfer
     */
    public static function build(string $disk, string $path, array $clipboard)
    {
        if ($disk !== $clipboard['disk']) {
            return new ExternalTransfer($disk, $path, $clipboard);
        }

        return new LocalTransfer($disk, $path, $clipboard);
    }
}
