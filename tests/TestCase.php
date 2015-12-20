<?php
namespace Czim\JsonApi\Test;

use Czim\JsonApi\JsonApiServiceProvider;
use Mockery;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // set bindings
        $app->register(JsonApiServiceProvider::class);

        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('jsonapi.base_url', 'http://www.test.com');
        $app['config']->set('jsonapi.base_path', 'v1');

        $app['config']->set('jsonapi.relations.always_show_data', [
            \Czim\JsonApi\Test\Helpers\Models\TestModel::class => [
                'testRelatedModels',
            ],
        ]);
    }

}
