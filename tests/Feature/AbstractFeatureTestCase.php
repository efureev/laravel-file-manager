<?php

declare(strict_types=1);

namespace Alexusmai\LaravelFileManager\Tests\Feature;

use Alexusmai\LaravelFileManager\Tests\AbstractTestCase;

abstract class AbstractFeatureTestCase extends AbstractTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
