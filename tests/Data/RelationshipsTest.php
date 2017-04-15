<?php
namespace Czim\JsonApi\Test\Data;

use Czim\JsonApi\Data\Relationship;
use Czim\JsonApi\Data\Relationships;
use Czim\JsonApi\Test\TestCase;

/**
 * Class RelationshipsTest
 *
 * @group data
 */
class RelationshipsTest extends TestCase
{

    /**
     * @test
     */
    function it_decorates_links_as_string_or_object()
    {
        $data = new Relationships([
            'comments' => [
                'data' => [
                    ['type' => 'comments', 'id' => '1'],
                ],
            ],
            'empty' => null,
        ]);

        static::assertInstanceOf(Relationship::class, $data->comments);
        static::assertNull($data->empty);
    }

}
