<?php

declare(strict_types=1);

namespace Alexusmai\LaravelFileManager\Exceptions;

class DirectoryExistsException extends Exception
{
    public function __construct($directory, $message = 'Directory exists')
    {
        parent::__construct(422, "$message: $directory");
    }
}
