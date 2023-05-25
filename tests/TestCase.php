<?php

namespace SignatureTech\ResponseBuilder\Tests;

use SignatureTech\ResponseBuilder\ResponseBuilderServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        // Additional setup

    }

    protected function getPackageProviders($app)
    {
        return [
            ResponseBuilderServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // perform environment setup
    }
}
