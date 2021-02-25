<?php

declare(strict_types=1);

namespace Alexusmai\LaravelFileManager\Exceptions;

class FileExistsException extends Exception
{
    public function __construct($file, $message = 'File exists')
    {
        parent::__construct(422, "$message: $file");
    }
}
