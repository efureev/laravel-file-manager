<?php

declare(strict_types=1);

namespace Alexusmai\LaravelFileManager\Events;

use Alexusmai\LaravelFileManager\Http\Requests\RequestValidator;

final class Download
{
    use BaseTrait;

    /**
     * Download constructor.
     *
     * @param RequestValidator $request
     */
    public function __construct(RequestValidator $request)
    {
        $this->disk = $request->disk();
        $this->path = $request->disk();
    }
}
