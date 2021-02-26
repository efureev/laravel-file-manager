<?php

declare(strict_types=1);

namespace Alexusmai\LaravelFileManager\Events;

use Alexusmai\LaravelFileManager\Http\Requests\RequestValidator;

final class Rename
{
    use DiskTrait;

    /**
     * @var string
     */
    private string $newName;

    /**
     * @var string
     */
    private string $oldName;

    /**
     * @var string
     */
    private string $type;

    /**
     * Rename constructor.
     *
     * @param RequestValidator $request
     */
    public function __construct(RequestValidator $request)
    {
        $this->disk    = $request->disk();
        $this->newName = $request->input('newName');
        $this->oldName = $request->input('oldName');
        $this->type    = $request->input('type');
    }

    /**
     * @return string
     */
    public function newName(): string
    {
        return $this->newName;
    }

    /**
     * @return string
     */
    public function oldName(): string
    {
        return $this->oldName;
    }

    /**
     * @return string
     */
    public function type(): string
    {
        /*
         *   $info = Storage::disk($this->disk)->getMetadata($this->oldName);
         *   return $info['type'];
         */

        return $this->type;
    }
}
