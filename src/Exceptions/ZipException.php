<?php

declare(strict_types=1);

namespace Alexusmai\LaravelFileManager\Exceptions;

class ZipException extends Exception
{
    public function __construct(string $message = 'Zip archive creating has failed')
    {
        parent::__construct(500, $message);
    }
}
