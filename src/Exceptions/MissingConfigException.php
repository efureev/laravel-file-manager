<?php

declare(strict_types=1);

namespace Alexusmai\LaravelFileManager\Exceptions;

class MissingConfigException extends Exception
{
    public function __construct($message = 'Missing config')
    {
        parent::__construct(422, $message);
    }
}
