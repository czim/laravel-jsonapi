<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\JsonApi\Test\Data;

use Czim\JsonApi\Data\Resource;
use Czim\JsonApi\Test\TestCase;

/**
 * Class ResourceTest
 *
 * @group data
 */
class ResourceTest extends TestCase
{

    /**
     * @test
     */
    function it_returns_whether_attributes_key_is_set()
    {
        $data = new Resource;

        static::assertFalse($data->hasAttributes());

        $data = new Resource(['attributes' => []]);

        static::assertTrue($data->hasAttributes());
    }

    /**
     * @test
     */
    function it_returns_whether_relationships_key_is_set()
    {
        $data = new Resource;

        static::assertFalse($data->hasRelationships());

        $data = new Resource(['relationships' => []]);

        static::assertTrue($data->hasRelationships());
    }

    /**
     * @test
     */
    function it_returns_whether_links_key_is_set()
    {
        $data = new Resource;

        static::assertFalse($data->hasLinks());

        $data = new Resource(['links' => []]);

        static::assertTrue($data->hasLinks());
    }

    /**
     * @test
     */
    function it_returns_whether_meta_key_is_set()
    {
        $data = new Resource;

        static::assertFalse($data->hasMeta());

        $data = new Resource(['meta' => []]);

        static::assertTrue($data->hasMeta());
    }

    /**
     * @test
     */
    function it_returns_whether_it_is_a_resource_identifier()
    {
        $data = new Resource;

        static::assertFalse($data->isResourceIdentifier());

        $data->type = 'test';
        $data->id   = '1';

        static::assertTrue($data->isResourceIdentifier());

        $data->attributes = [
            'title' => 'testing',
        ];

        static::assertFalse($data->isResourceIdentifier());
    }

}
