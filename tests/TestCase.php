<?php

namespace EmailChangeVerification\Tests;

use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            \EmailChangeVerification\ServiceProvider::class,
        ];
    }

    public function defineEnvironment($app)
    {
        // $app['config']->set('email-change-verification.default', 'users');
    }
}
