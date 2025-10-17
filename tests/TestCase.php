<?php

declare(strict_types=1);

namespace NickSdot\BladeHtmlAttributes\Tests;

use NickSdot\BladeHtmlAttributes\BladeHtmlAttributesServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            BladeHtmlAttributesServiceProvider::class,
        ];
    }
}
