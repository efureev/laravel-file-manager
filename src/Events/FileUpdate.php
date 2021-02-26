<?php

declare(strict_types=1);

namespace Alexusmai\LaravelFileManager\Events;

use Alexusmai\LaravelFileManager\Http\Requests\RequestValidator;

final class FileUpdate
{
    use BaseTrait;

    /**
     * @var \Illuminate\Http\UploadedFile
     */
    private $file;

    /**
     * FileUpdate constructor.
     *
     * @param RequestValidator $request
     */
    public function __construct(RequestValidator $request)
    {
        $this->disk = $request->disk();
        $this->path = $request->path();
        $this->file = $request->file('file');
    }

    /**
     * @return string
     */
    public function path(): string
    {
        if ($this->path) {
            return $this->path . '/' . $this->file->getClientOriginalName();
        }

        return $this->file->getClientOriginalName();
    }
}
