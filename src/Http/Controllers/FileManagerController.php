<?php

declare(strict_types=1);

namespace Alexusmai\LaravelFileManager\Http\Controllers;

use Alexusmai\LaravelFileManager\Events\BeforeInitialization;
use Alexusmai\LaravelFileManager\Events\Deleting;
use Alexusmai\LaravelFileManager\Events\DirectoryCreated;
use Alexusmai\LaravelFileManager\Events\DirectoryCreating;
use Alexusmai\LaravelFileManager\Events\DiskSelected;
use Alexusmai\LaravelFileManager\Events\Download;
use Alexusmai\LaravelFileManager\Events\FileCreated;
use Alexusmai\LaravelFileManager\Events\FileCreating;
use Alexusmai\LaravelFileManager\Events\FilesUploaded;
use Alexusmai\LaravelFileManager\Events\FilesUploading;
use Alexusmai\LaravelFileManager\Events\FileUpdate;
use Alexusmai\LaravelFileManager\Events\Paste;
use Alexusmai\LaravelFileManager\Events\Rename;
use Alexusmai\LaravelFileManager\Events\Unzip as UnzipEvent;
use Alexusmai\LaravelFileManager\Events\Zip as ZipEvent;
use Alexusmai\LaravelFileManager\FileManager;
use Alexusmai\LaravelFileManager\Http\Requests\RequestValidator;
use Alexusmai\LaravelFileManager\Services\Zip;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class FileManagerController extends Controller
{
    /**
     * @var FileManager
     */
    public FileManager $fm;

    /**
     * FileManagerController constructor.
     *
     * @param FileManager $fm
     */
    public function __construct(FileManager $fm)
    {
        $this->fm = $fm;
    }

    /**
     * Initialize file manager
     *
     * @return JsonResource
     */
    public function initialize(): JsonResource
    {
        event(new BeforeInitialization());

        return new JsonResource($this->fm->initializeConfig());
    }

    /**
     * Get files and directories for the selected path and disk
     *
     * @param RequestValidator $request
     *
     * @return JsonResource
     */
    public function content(RequestValidator $request): JsonResource
    {
        return new JsonResource(
            $this->fm->content(
                $request->input('disk'),
                $request->input('path')
            )
        );
    }

    /**
     * Directory tree
     *
     * @param RequestValidator $request
     *
     * @return JsonResource
     */
    public function tree(RequestValidator $request): JsonResource
    {
        return new JsonResource(
            $this->fm->tree(
                $request->input('disk'),
                $request->input('path')
            )
        );
    }

    /**
     * Upload files
     *
     * @param RequestValidator $request
     *
     * @return JsonResource
     */
    public function upload(RequestValidator $request): JsonResource
    {
        event(new FilesUploading($request));

        $uploadFiles = $this->fm->upload(
            $request->disk(),
            $request->path(),
            $request->files(),
            $request->input('overwrite', false)
        );

        event(new FilesUploaded($request, $uploadFiles));

        return new JsonResource($uploadFiles);
    }

    /**
     * File url
     *
     * @param RequestValidator $request
     *
     * @return JsonResource
     */
    public function url(RequestValidator $request): JsonResource
    {
        $url = $this->fm->url($request->input('disk'), $request->input('path'));

        return new JsonResource(['url' => $url]);
    }




    /*public function content(RequestValidator $request)
    {
        return response()->json(
            $this->fm->content(
                $request->input('disk'),
                $request->input('path')
            )
        );
    }*/

    /**
     * Check the selected disk
     *
     * @param RequestValidator $request
     *
     * @return JsonResource
     */
    public function selectDisk(RequestValidator $request): JsonResource
    {
        event(new DiskSelected($request->input('disk')));

        return new JsonResource(
            [
                'message' => 'diskSelected',
            ]
        );
    }


    /**
     * Delete files and folders
     *
     * @param RequestValidator $request
     *
     * @return JsonResource
     */
    public function delete(RequestValidator $request): JsonResource
    {
        event(new Deleting($request));

        $deletedItems = $this->fm->delete(
            $request->input('disk'),
            $request->input('items')
        );

        return new JsonResource($deletedItems);
    }

    /**
     * Copy / Cut files and folders
     *
     * @param RequestValidator $request
     *
     * @return JsonResource
     */
    public function paste(RequestValidator $request): JsonResource
    {
        event(new Paste($request));

        return new JsonResource(
            $this->fm->paste(
                $request->input('disk'),
                $request->input('path'),
                $request->input('clipboard')
            )
        );
    }

    /**
     * Rename
     *
     * @param RequestValidator $request
     *
     * @return Response
     */
    public function rename(RequestValidator $request): Response
    {
        event(new Rename($request));

        $this->fm->rename(
            $request->input('disk'),
            $request->input('newName'),
            $request->input('oldName')
        );

        return response()->noContent();
    }

    /**
     * Download file
     *
     * @param RequestValidator $request
     *
     * @return mixed
     */
    public function download(RequestValidator $request)
    {
        event(new Download($request));

        return $this->fm->download(
            $request->input('disk'),
            $request->input('path')
        );
    }

    /**
     * Create thumbnails
     *
     * @param RequestValidator $request
     *
     * @return Response
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function thumbnails(RequestValidator $request): Response
    {
        $thumbnail = $this->fm->thumbnails(
            $request->input('disk'),
            $request->input('path')
        );
        return $thumbnail->response();
        // output
        /*return response()->make(
            $thumbnail,
            200,
            ['Content-Type' => Storage::disk($request->input('disk'))->mimeType($request->input('path'))]
        );*/
    }

    /**
     * Image preview
     *
     * @param RequestValidator $request
     *
     * @return mixed
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function preview(RequestValidator $request)
    {
        return $this->fm->preview(
            $request->input('disk'),
            $request->input('path')
        );
    }


    /**
     * Create new directory
     *
     * @param RequestValidator $request
     *
     * @return JsonResource
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function createDirectory(RequestValidator $request): JsonResource
    {
        event(new DirectoryCreating($request));

        $createDirectoryResponse = $this->fm->createDirectory(
            $request->disk(),
            $request->path(),
            $request->input('name')
        );

        event(new DirectoryCreated($request));

        return new JsonResource($createDirectoryResponse);
    }

    /**
     * Create new file
     *
     * @param RequestValidator $request
     *
     * @return JsonResource
     */
    public function createFile(RequestValidator $request): JsonResource
    {
        event(new FileCreating($request));

        $createFileResponse = $this->fm->createFile(
            $request->input('disk'),
            $request->input('path'),
            $request->input('name')
        );

        event(new FileCreated($request));

        return new JsonResource(
            [
                'file' => $createFileResponse,
            ]
        );
    }

    /**
     * Update file
     *
     * @param RequestValidator $request
     *
     * @return JsonResource
     */
    public function updateFile(RequestValidator $request): JsonResource
    {
        event(new FileUpdate($request));

        return new JsonResource(
            [
                'file' => $this->fm->updateFile(
                    $request->input('disk'),
                    $request->input('path'),
                    $request->file('file')
                ),
            ]
        );
    }

    /**
     * Stream file
     *
     * @param RequestValidator $request
     *
     * @return mixed
     */
    public function streamFile(RequestValidator $request)
    {
        return $this->fm->streamFile(
            $request->input('disk'),
            $request->input('path')
        );
    }

    /**
     * Create zip archive
     *
     * @param RequestValidator $request
     * @param Zip $zip
     *
     * @return Response
     */
    public function zip(RequestValidator $request, Zip $zip): Response
    {
        event(new ZipEvent($request));

        $zip->create();

        return response()->noContent();
    }

    /**
     * Extract zip archive
     *
     * @param RequestValidator $request
     * @param Zip $zip
     *
     * @return Response
     */
    public function unzip(RequestValidator $request, Zip $zip): Response
    {
        event(new UnzipEvent($request));
        $zip->extract();

        return response()->noContent();
    }

    /**
     * Integration with ckeditor 4
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    /*public function ckeditor()
    {
        return view('file-manager::ckeditor');
    }*/

    /**
     * Integration with TinyMCE v4
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    /* public function tinymce()
     {
         return view('file-manager::tinymce');
     }*/

    /**
     * Integration with TinyMCE v5
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    /*public function tinymce5()
    {
        return view('file-manager::tinymce5');
    }*/

    /**
     * Integration with SummerNote
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    /*public function summernote()
    {
        return view('file-manager::summernote');
    }*/

    /**
     * Simple integration with input field
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    /*public function fmButton()
    {
        return view('file-manager::fmButton');
    }*/
}
