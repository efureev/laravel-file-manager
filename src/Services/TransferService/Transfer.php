<?php

namespace Alexusmai\LaravelFileManager\Services\TransferService;

use Alexusmai\LaravelFileManager\Exceptions\FileTransferException;

abstract class Transfer
{
    public string $disk;
    public string $path;
    public array $clipboard;

    /**
     * Transfer constructor.
     *
     * @param $disk
     * @param $path
     * @param $clipboard
     */
    public function __construct(string $disk, string $path, array $clipboard)
    {
        $this->disk      = $disk;
        $this->path      = $path;
        $this->clipboard = $clipboard;
    }

    /**
     * Transfer files and folders
     */
    public function filesTransfer(): void
    {
        try {
            switch ($this->clipboard['type']) {
                case 'copy':
                    $this->copy();
                    break;
                case 'cut':
                    $this->cut();
                    break;
            }
        } catch (\Exception $exception) {
            FileTransferException::throw($exception->getMessage());
        }
    }

    abstract protected function copy(): void;

    abstract protected function cut(): void;
}
