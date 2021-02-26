<?php

declare(strict_types=1);

namespace Alexusmai\LaravelFileManager\Events;

use Alexusmai\LaravelFileManager\Http\Requests\RequestValidator;

class Zip
{
    use BaseTrait;


    private string $name;

    /**
     * @var array|string|null
     */
    private $elements;

    /**
     * Zip constructor.
     *
     * @param RequestValidator $request
     */
    public function __construct(RequestValidator $request)
    {
        $this->disk     = $request->disk();
        $this->path     = $request->path();
        $this->name     = $request->input('name');
        $this->elements = $request->input('elements');
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return array|string|null
     */
    public function elements()
    {
        return $this->elements;
    }
}
