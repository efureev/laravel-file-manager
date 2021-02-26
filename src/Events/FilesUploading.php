<?php

declare(strict_types=1);

namespace Alexusmai\LaravelFileManager\Events;

use Alexusmai\LaravelFileManager\Http\Requests\RequestValidator;

class FilesUploading
{
    use BaseTrait;

    /**
     * @var \Illuminate\Http\UploadedFile[]
     */
    private array $files;

    private bool $overwrite;

    /**
     * FilesUploading constructor.
     *
     * @param RequestValidator $request
     */
    public function __construct(RequestValidator $request)
    {
        $this->disk      = $request->disk();
        $this->path      = $request->path();
        $this->files     = $request->files();
        $this->overwrite = (bool)isTrue($request->input('overwrite'));
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
