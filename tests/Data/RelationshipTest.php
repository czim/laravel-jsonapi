<?php
namespace Czim\JsonApi\Test\Data;

use Czim\JsonApi\Data\Relationship;
use Czim\JsonApi\Data\Resource;
use Czim\JsonApi\Test\TestCase;

/**
 * Class RelationshipTest
 *
 * @group data
 */
class RelationshipTest extends TestCase
{

    /**
     * @test
     */
    function it_decorates_data_as_null_resource_or_array_of_resources()
    {
        $data = new Relationship([
            'data' => null,
        ]);

        static::assertNull($data->data);

        $data = new Relationship([
            'data' => ['type' => 'test', 'id' => '1'],
        ]);

        static::assertInstanceOf(Resource::class, $data->data);
        // Load it again to check when already eager loaded
        static::assertInstanceOf(Resource::class, $data->data);

        $data = new Relationship([
            'data' => [
                ['type' => 'test', 'id' => '1'],
                null,
            ],
        ]);

        static::assertIsArray($data->data);
        static::assertInstanceOf(Resource::class, head($data->data));
        static::assertNull(last($data->data));
    }

}
