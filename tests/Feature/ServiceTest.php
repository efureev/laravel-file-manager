<?php

declare(strict_types=1);

namespace Alexusmai\LaravelFileManager\Tests\Feature;

use Alexusmai\LaravelFileManager\FileManager;
use Alexusmai\LaravelFileManager\Services\ConfigService\ConfigRepository;
use Alexusmai\LaravelFileManager\Services\ConfigService\DefaultConfigRepository;
use Illuminate\Http\UploadedFile;

class ServiceTest extends AbstractFeatureTestCase
{
    public function testConfigsInstance(): void
    {
        static::assertInstanceOf(DefaultConfigRepository::class, app(ConfigRepository::class));
        $config = app(ConfigRepository::class);
        static::assertEquals('file-manager', $config->getRoutePrefix());
        static::assertEquals(['public'], $config->getDiskList());
    }

    public function testFileManagerInstance(): void
    {
        static::assertInstanceOf(FileManager::class, app(FileManager::class));
        $fm = app(FileManager::class);
        static::assertInstanceOf(DefaultConfigRepository::class, $fm->configRepository);
        //        dd($fm->initializeConfig());
    }

    public function testInitializeConfig(): void
    {
        $data = app(FileManager::class)->initializeConfig();
        static::assertEquals(
            [
                "acl"           => false,
                "leftDisk"      => null,
                "rightDisk"     => null,
                "leftPath"      => null,
                "rightPath"     => null,
                "windowsConfig" => 2,
                "hiddenFiles"   => true,
                "disks"         => [
                    "public" => [
                        "driver" => "local",
                    ],
                ],
                "lang"          => "en",
            ],
            $data
        );
    }

    public function testUpload(): void
    {
        /** @var FileManager $fm */

        $fm        = app(FileManager::class);
        $filesName = ['image-50x50.jpg', 'document-text.txt'];
        $files     = array_map(
            static fn($fn) => new UploadedFile(__DIR__ . "/../mocks/$fn", $fn),
            $filesName
        );
        $disk      = config('filesystems.default');

        $result           = $fm->upload($disk, 'tmp', $files, false);
        $receiveFileNames = array_map(
            static fn($fn) => "tmp/$fn",
            $filesName
        );

        static::assertCount(count($filesName), $result);
        static::assertEquals($receiveFileNames, array_keys($result));
    }

    public function testContent(): void
    {
        /** @var FileManager $fm */
        $fm        = app(FileManager::class);
        $filesName = ['image-50x50.jpg', 'document-text.txt'];
        $files     = array_map(
            static fn($fn) => new UploadedFile(__DIR__ . "/../mocks/$fn", $fn),
            $filesName
        );

        $disk = config('filesystems.default');

        $fm->upload($disk, 'tmp', $files, false);

        $result = $fm->content($disk);
        static::assertEmpty($result['files']);
        static::assertCount(1, $result['directories']);

        $directory = $result['directories'][0];
        static::assertArrayHasKey('basename', $directory);
        static::assertEquals('tmp', $directory['basename']);
        static::assertEquals('dir', $directory['type']);
        static::assertArrayHasKey('dirname', $directory);


        $result = $fm->content($disk, 'tmp');

        static::assertEmpty($result['directories']);
        static::assertCount(2, $result['files']);
    }
}
