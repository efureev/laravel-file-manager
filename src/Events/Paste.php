<?php

declare(strict_types=1);

namespace Alexusmai\LaravelFileManager\Events;

use Alexusmai\LaravelFileManager\Http\Requests\RequestValidator;

final class Paste
{
    use BaseTrait;

    private array $clipboard;

    /**
     * Paste constructor.
     *
     * @param RequestValidator $request
     */
    public function __construct(RequestValidator $request)
    {
        $this->disk      = $request->disk();
        $this->path      = $request->path();
        $this->clipboard = $request->input('clipboard');
    }

    /**
     * @return array
     */
    public function clipboard(): array
    {
        return $this->clipboard;
    }
}
