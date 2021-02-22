<?php

declare(strict_types=1);

namespace Alexusmai\LaravelFileManager\Exceptions;

class FailedUploadException extends Exception
{
    public function __construct($message = 'Failed upload')
    {
        parent::__construct(422, $message);
    }
}
