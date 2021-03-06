<?php

declare(strict_types=1);

namespace Alexusmai\LaravelFileManager\Events;

use Alexusmai\LaravelFileManager\Http\Requests\RequestValidator;

class FileCreating
{
    use BaseTrait;

    private string $name;

    /**
     * FileCreating constructor.
     *
     * @param RequestValidator $request
     */
    public function __construct(RequestValidator $request)
    {
        $this->disk = $request->disk();
        $this->path = $request->path();
        $this->name = $request->input('name');
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }
}
