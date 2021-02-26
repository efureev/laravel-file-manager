<?php

declare(strict_types=1);

namespace Alexusmai\LaravelFileManager\Events;

use Alexusmai\LaravelFileManager\Http\Requests\RequestValidator;

class Unzip
{
    use BaseTrait;

    /**
     * @var string
     */
    private string $folder;

    /**
     * Unzip constructor.
     *
     * @param RequestValidator $request
     */
    public function __construct(RequestValidator $request)
    {
        $this->disk   = $request->disk();
        $this->path   = $request->path();
        $this->folder = $request->input('folder');
    }

    /**
     * @return string
     */
    public function folder(): string
    {
        return $this->folder;
    }
}
