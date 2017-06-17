<?php
namespace Czim\JsonApi\Test;

use Czim\JsonApi\Providers\JsonApiServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app->register(JsonApiServiceProvider::class);

        $app->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            \Czim\JsonApi\Test\Helpers\Exceptions\Handler::class
        );

        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

}
