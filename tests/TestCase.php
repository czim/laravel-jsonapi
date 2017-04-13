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

        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    /**
     * Performs simple assertion of validity of JSON-API serialized data.
     *
     * @param mixed|array     $data
     * @param string          $type
     * @param string          $id
     * @param array           $attributes   if nonempty, attributes to match the values for
     */
    protected function assertBasicJsonApiResponse($data, $type, $id, $attributes = [])
    {
        static::assertInternalType('array', $data);
        static::assertArrayHasKey('data', $data);

        static::assertArrayHasKey('type', $data['data'], "Data has no 'type' key");
        static::assertEquals($type, $data['data']['type']);

        static::assertArrayHasKey('id', $data['data'], "Data has no 'id' key");
        static::assertSame($id, $data['data']['id']);

        if ( ! empty($attributes)) {
            static::assertArrayHasKey('attributes', $data['data']);

            foreach ($attributes as $key => $value) {
                static::assertArrayHasKey($key, $data['data']['attributes'], "Attributes does not have key '{$key}''");
                static::assertSame($value, $data['data']['attributes'][$key], "Attribute value for {$key} is incorrect");
            }
        }
    }
}
