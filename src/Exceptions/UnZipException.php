<?php

declare(strict_types=1);

namespace Alexusmai\LaravelFileManager\Exceptions;

class UnZipException extends Exception
{
    public function __construct(string $message = 'Zip extraction has failed')
    {
        parent::__construct(500, $message);
    }
}
