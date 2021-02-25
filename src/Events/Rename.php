<?php

namespace Alexusmai\LaravelFileManager\Events;

use Illuminate\Http\Request;

class Rename
{
    /**
     * @var string
     */
    private string $disk;

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
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->disk    = $request->input('disk');
        $this->newName = $request->input('newName');
        $this->oldName = $request->input('oldName');
        $this->type    = $request->input('type');
    }

    /**
     * @return string
     */
    public function disk(): string
    {
        return $this->disk;
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
