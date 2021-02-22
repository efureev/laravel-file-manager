<?php

declare(strict_types=1);

namespace Alexusmai\LaravelFileManager\Events;

use Illuminate\Http\Request;

class FilesUploading
{
    /**
     * @var string
     */
    private string $disk;

    /**
     * @var string
     */
    private string $path;

    /**
     * @var \Illuminate\Http\UploadedFile[]
     */
    private array $files;

    /**
     * @var string|null
     */
    private bool $overwrite;

    /**
     * FilesUploading constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->disk      = $request->input('disk');
        $this->path      = $request->input('path');
        $this->files     = ($request->file('files') ?? []);
        $this->overwrite = (bool)isTrue($request->input('overwrite'));
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
    public function path(): string
    {
        return $this->path;
    }

    /**
     * @return array
     */
    public function files(): array
    {
        return array_map(
            fn($file) => [
                'name'      => $file->getClientOriginalName(),
                'path'      => $this->path . '/' . $file->getClientOriginalName(),
                'extension' => $file->extension(),
            ],
            $this->files
        );
    }

    /**
     * @return bool
     */
    public function overwrite(): bool
    {
        return $this->overwrite;
    }
}
