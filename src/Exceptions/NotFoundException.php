<?php

declare(strict_types=1);

namespace Alexusmai\LaravelFileManager\Exceptions;

class NotFoundException extends Exception
{
    public function __construct(string $message = 'Not found')
    {
        parent::__construct(404, $message);
    }
}
