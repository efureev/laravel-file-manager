<?php

declare(strict_types=1);

namespace Alexusmai\LaravelFileManager\Tests;

use Alexusmai\LaravelFileManager\FileManagerServiceProvider;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Config;
use Orchestra\Testbench\TestCase;

/**
 * Class AbstractTestCase
 */
abstract class AbstractTestCase extends TestCase
{

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        /** @var \Illuminate\Filesystem\FilesystemAdapter $driver */
        $driver = Storage::disk(config('filesystems.default'));
        /** @var \League\Flysystem\Adapter\Local $adapter */
        $adapter = $driver->getAdapter();

        $adapter->setPathPrefix(realpath(__DIR__));
        $adapter->createDir('storage', new Config());
        $adapter->setPathPrefix(realpath(__DIR__ . '/storage'));


        $this->app->useStoragePath($driver->path(''));
    }

    protected function tearDown(): void
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $driver */
        $driver = Storage::disk(config('filesystems.default'));
        /** @var \League\Flysystem\Adapter\Local $adapter */
        $adapter = $driver->getAdapter();
        $adapter->setPathPrefix(realpath(__DIR__));

        $adapter->deleteDir('storage');

        parent::tearDown();
    }

    protected function getPackageProviders($app): array
    {
        return [
            FileManagerServiceProvider::class,
        ];
    }
}
