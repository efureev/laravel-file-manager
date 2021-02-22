<?php

declare(strict_types=1);

namespace Alexusmai\LaravelFileManager\Exceptions;

use Php\Support\Traits\Thrower;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Exception extends HttpException
{
    use Thrower;
}
