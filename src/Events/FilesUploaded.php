<?php

declare(strict_types=1);

namespace Alexusmai\LaravelFileManager\Events;

use Alexusmai\LaravelFileManager\Http\Requests\RequestValidator;

final class FilesUploaded extends FilesUploading
{
    /** @var array Закачанные файлы */
    private array $uploadFiles;

    /**
     * FilesUploaded constructor.
     *
     * @param RequestValidator $request
     * @param array $uploadFiles
     */
    public function __construct(RequestValidator $request, array $uploadFiles)
    {
        parent::__construct($request);

        $this->uploadFiles = $uploadFiles;
    }

    /**
     * @return array
     */
    public function uploadFiles(): array
    {
        return $this->uploadFiles;
    }

}
