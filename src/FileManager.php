<?php

declare(strict_types=1);

namespace Alexusmai\LaravelFileManager;

use Alexusmai\LaravelFileManager\Events\Deleted;
use Alexusmai\LaravelFileManager\Events\Deleting;
use Alexusmai\LaravelFileManager\Exceptions\DirectoryExistsException;
use Alexusmai\LaravelFileManager\Exceptions\FailedUploadException;
use Alexusmai\LaravelFileManager\Exceptions\FileExistsException;
use Alexusmai\LaravelFileManager\Exceptions\MissingConfigException;
use Alexusmai\LaravelFileManager\Exceptions\NotFoundException;
use Alexusmai\LaravelFileManager\Services\ConfigService\ConfigRepository;
use Alexusmai\LaravelFileManager\Services\TransferService\TransferFactory;
use Alexusmai\LaravelFileManager\Traits\CheckTrait;
use Alexusmai\LaravelFileManager\Traits\ContentTrait;
use Alexusmai\LaravelFileManager\Traits\PathTrait;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class FileManager
{
    use PathTrait;
    use ContentTrait;
    use CheckTrait;

    /**
     * @var ConfigRepository
     */
    public $configRepository;

    /**
     * FileManager constructor.
     *
     * @param ConfigRepository $configRepository
     */
    public function __construct(ConfigRepository $configRepository)
    {
        $this->configRepository = $configRepository;
    }

    /**
     * Initialize App
     *
     * @return array
     */
    public function initializeConfig(): array
    {
        MissingConfigException::throwIf(!config()->has('file-manager'));

        $config = [
            'acl'           => $this->configRepository->getAcl(),
            'leftDisk'      => $this->configRepository->getLeftDisk(),
            'rightDisk'     => $this->configRepository->getRightDisk(),
            'leftPath'      => $this->configRepository->getLeftPath(),
            'rightPath'     => $this->configRepository->getRightPath(),
            'windowsConfig' => $this->configRepository->getWindowsConfig(),
            'hiddenFiles'   => $this->configRepository->getHiddenFiles(),
        ];

        // disk list
        foreach ($this->configRepository->getDiskList() as $disk) {
            if (array_key_exists($disk, config('filesystems.disks'))) {
                $config['disks'][$disk] = Arr::only(
                    config('filesystems.disks')[$disk],
                    ['driver']
                );
            }
        }

        // get language
        $config['lang'] = app()->getLocale();

        return $config;
    }
    /*
        public function initialize(): array
        {

            // if config not found
            if (!config()->has('file-manager')) {
                return [
                    'result' => [
                        'status'  => 'danger',
                        'message' => 'noConfig',
                    ],
                ];
            }

            $config = [
                'acl'           => $this->configRepository->getAcl(),
                'leftDisk'      => $this->configRepository->getLeftDisk(),
                'rightDisk'     => $this->configRepository->getRightDisk(),
                'leftPath'      => $this->configRepository->getLeftPath(),
                'rightPath'     => $this->configRepository->getRightPath(),
                'windowsConfig' => $this->configRepository->getWindowsConfig(),
                'hiddenFiles'   => $this->configRepository->getHiddenFiles(),
            ];

            // disk list
            foreach ($this->configRepository->getDiskList() as $disk) {
                if (array_key_exists($disk, config('filesystems.disks'))) {
                    $config['disks'][$disk] = Arr::only(
                        config('filesystems.disks')[$disk],
                        ['driver']
                    );
                }
            }

            // get language
            $config['lang'] = app()->getLocale();

            return [
                'result' => [
                    'status'  => 'success',
                    'message' => null,
                ],
                'config' => $config,
            ];
        }
    */

    /**
     * Get files and directories for the selected path and disk
     *
     * @param string $disk
     * @param string $path
     *
     * @return array
     */
    public function content(string $disk, string $path): array
    {
        // get content for the selected directory
        $content = $this->getContent($disk, $path);

        return [
            'directories' => $content['directories'],
            'files'       => $content['files'],
        ];
    }

    /*public function content($disk, $path): array
    {
        // get content for the selected directory
        $content = $this->getContent($disk, $path);

        return [
            'result'      => [
                'status'  => 'success',
                'message' => null,
            ],
            'directories' => $content['directories'],
            'files'       => $content['files'],
        ];
    }*/

    /**
     * Get part of the directory tree
     *
     * @param string $disk
     * @param string $path
     *
     * @return array
     */
    public function tree(string $disk, string $path): array
    {
        return $this->getDirectoriesTree($disk, $path);
    }
    /*public function tree($disk, $path): array
    {
        $directories = $this->getDirectoriesTree($disk, $path);

        return [
            'result'      => [
                'status'  => 'success',
                'message' => null,
            ],
            'directories' => $directories,
        ];
    }*/

    /**
     * Upload files
     *
     * @param string $disk
     * @param string $path
     * @param array $files
     * @param bool $overwrite
     *
     * @return array
     */
    public function upload(string $disk, string $path, array $files, bool $overwrite): array
    {
        $filesUploaded = [];

        foreach ($files as $file) {
            // skip or overwrite files
            if (!$overwrite && Storage::disk($disk)->exists($path . '/' . $file->getClientOriginalName())) {
                continue;
            }

            // check file size if need
            if ($this->configRepository->getMaxUploadFileSize() &&
                ($file->getSize() / 1024) > $this->configRepository->getMaxUploadFileSize()
            ) {
                continue;
            }

            // check file type if need
            if ($this->configRepository->getAllowFileTypes() &&
                !in_array($file->getClientOriginalExtension(), $this->configRepository->getAllowFileTypes(), true)
            ) {
                continue;
            }

            // overwrite or save file
            if ($res = Storage::disk($disk)
                ->putFileAs(
                    $path,
                    $file,
                    $file->getClientOriginalName()
                )
            ) {
                $filesUploaded[$res] = $file;
            }
        }

        FailedUploadException::throwIf(count($filesUploaded) < count($files));

        return $filesUploaded;
    }

    /**
     * Delete files and folders
     *
     * @param string $disk
     * @param array $items
     *
     * @return array
     */
    public function delete(string $disk, array $items): array
    {
        event(new Deleting($disk, $items));

        $deletedItems = [];

        foreach ($items as $item) {
            // check all files and folders - exists or no
            if (!Storage::disk($disk)->exists($item['path'])) {
                continue;
            }
            if ($item['type'] === 'dir') {
                // delete directory
                Storage::disk($disk)->deleteDirectory($item['path']);
            } else {
                // delete file
                Storage::disk($disk)->delete($item['path']);
            }

            // add deleted item
            $deletedItems[] = $item;
        }

        event(new Deleted($disk, $deletedItems));

        return $deletedItems;
    }

    /**
     * Copy / Cut - Files and Directories
     *
     * @param string $disk
     * @param string $path
     * @param array $clipboard
     *
     */
    public function paste(string $disk, string $path, array $clipboard): void
    {
        // compare disk names
        if ($disk !== $clipboard['disk']) {
            NotFoundException::throwIf(!$this->checkDisk($clipboard['disk']), 'Disk not found');
        }

        $transferService = TransferFactory::build($disk, $path, $clipboard);

        $transferService->filesTransfer();
    }

    /**
     * Rename file or folder
     *
     * @param string $disk
     * @param string $newName
     * @param string $oldName
     *
     * @return bool
     */
    public function rename(string $disk, string $newName, string $oldName): bool
    {
        return Storage::disk($disk)->move($oldName, $newName);
    }

    /**
     * Download selected file
     *
     * @param string $disk
     * @param string $path
     *
     * @return mixed
     */
    public function download(string $disk, string $path)
    {
        // if file name not in ASCII format
        if (!preg_match('/^[\x20-\x7e]*$/', basename($path))) {
            $filename = Str::ascii(basename($path));
        } else {
            $filename = basename($path);
        }

        return Storage::disk($disk)->download($path, $filename);
    }

    /**
     * Create thumbnails
     *
     * @param string $disk
     * @param string $path
     *
     * @return \Intervention\Image\Image
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function thumbnails(string $disk, string $path): \Intervention\Image\Image
    {
        // create thumbnail
        if ($this->configRepository->getCache()) {
            return Image::cache(
                function ($image) use ($disk, $path) {
                    $image->make(Storage::disk($disk)->get($path))->fit(80);
                },
                $this->configRepository->getCache()
            );
        }

        return Image::make(Storage::disk($disk)->get($path))->fit(80);
    }


    /*public function thumbnails($disk, $path)
    {
        // create thumbnail
        if ($this->configRepository->getCache()) {
            $thumbnail = Image::cache(
                function ($image) use ($disk, $path) {
                    $image->make(Storage::disk($disk)->get($path))->fit(80);
                },
                $this->configRepository->getCache()
            );

            // output
            return response()->make(
                $thumbnail,
                200,
                ['Content-Type' => Storage::disk($disk)->mimeType($path)]
            );
        }

        $thumbnail= Image::make(Storage::disk($disk)->get($path))->fit(80);

        return $thumbnail->response();
    }*/

    /**
     * Image preview
     *
     * @param string $disk
     * @param string $path
     *
     * @return mixed
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function preview(string $disk, string $path)
    {
        // get image
        $preview = Image::make(Storage::disk($disk)->get($path));

        return $preview->response();
    }

    /**
     * Get file URL
     *
     * @param string $disk
     * @param string $path
     *
     * @return string
     */
    public function url(string $disk, string $path): string
    {
        return Storage::disk($disk)->url($path);
    }

    /*public function url($disk, $path)
    {
        return [
            'result' => [
                'status'  => 'success',
                'message' => null,
            ],
            'url'    => Storage::disk($disk)->url($path),
        ];
    }*/

    /**
     * Create new directory
     *
     * @param string $disk
     * @param string $path
     * @param string $name
     *
     * @return array
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function createDirectory(string $disk, string $path, string $name): array
    {
        // path for new directory
        $directoryName = static::newPath($path, $name);

        // check - exist directory or no
        if (Storage::disk($disk)->exists($directoryName)) {
            DirectoryExistsException::throw($directoryName);
        }

        // create new directory
        Storage::disk($disk)->makeDirectory($directoryName);

        // get directory properties
        $directoryProperties = $this->directoryProperties(
            $disk,
            $directoryName
        );

        // add directory properties for the tree module
        $tree          = $directoryProperties;
        $tree['props'] = ['hasSubdirectories' => false];

        return [
            'directory' => $directoryProperties,
            'tree'      => [$tree],
        ];
    }


    /*public function createDirectory($disk, $path, $name)
    {
        // path for new directory
        $directoryName = $this->newPath($path, $name);

        // check - exist directory or no
        if (Storage::disk($disk)->exists($directoryName)) {
            return [
                'result' => [
                    'status'  => 'warning',
                    'message' => 'dirExist',
                ],
            ];
        }

        // create new directory
        Storage::disk($disk)->makeDirectory($directoryName);

        // get directory properties
        $directoryProperties = $this->directoryProperties(
            $disk,
            $directoryName
        );

        // add directory properties for the tree module
        $tree          = $directoryProperties;
        $tree['props'] = ['hasSubdirectories' => false];

        return [
            'result'    => [
                'status'  => 'success',
                'message' => 'dirCreated',
            ],
            'directory' => $directoryProperties,
            'tree'      => [$tree],
        ];
    }*/

    /**
     * Create new file
     *
     * @param string $disk
     * @param string $path
     * @param string $name
     *
     * @return array
     */
    public function createFile(string $disk, string $path, string $name): array
    {
        // path for new file
        $path = static::newPath($path, $name);

        // check - exist file or no
        if (Storage::disk($disk)->exists($path)) {
            FileExistsException::throw($path);
        }

        // create new file
        Storage::disk($disk)->put($path, '');

        // get file properties
        return $this->fileProperties($disk, $path);
    }

    /**
     * Update file
     *
     * @param string $disk
     * @param string $path
     * @param $file
     *
     * @return array
     */
    public function updateFile(string $disk, string $path, $file): array
    {
        // update file
        Storage::disk($disk)->putFileAs(
            $path,
            $file,
            $file->getClientOriginalName()
        );

        // path for new file
        $filePath = static::newPath($path, $file->getClientOriginalName());

        // get file properties
        return $this->fileProperties($disk, $filePath);
    }

    /*public function updateFile($disk, $path, $file)
    {
        // update file
        Storage::disk($disk)->putFileAs(
            $path,
            $file,
            $file->getClientOriginalName()
        );

        // path for new file
        $filePath = static::newPath($path, $file->getClientOriginalName());

        // get file properties
        $fileProperties = $this->fileProperties($disk, $filePath);

        return [
            'result' => [
                'status'  => 'success',
                'message' => 'fileUpdated',
            ],
            'file'   => $fileProperties,
        ];
    }*/

    /**
     * Stream file - for audio and video
     *
     * @param string $disk
     * @param string $path
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function streamFile(string $disk, string $path)
    {
        // if file name not in ASCII format
        if (!preg_match('/^[\x20-\x7e]*$/', basename($path))) {
            $filename = Str::ascii(basename($path));
        } else {
            $filename = basename($path);
        }

        return Storage::disk($disk)
            ->response($path, $filename, ['Accept-Ranges' => 'bytes']);
    }
}
