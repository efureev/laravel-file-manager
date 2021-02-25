<?php

declare(strict_types=1);

namespace Alexusmai\LaravelFileManager\Exceptions;

class FileTransferException extends Exception
{
    public function __construct(string $msg, string $message = 'File transfer has failed')
    {
        parent::__construct(500, "$message: $msg");
    }
}
