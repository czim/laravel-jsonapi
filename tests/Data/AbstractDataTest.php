<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\JsonApi\Test\Data;

use Czim\JsonApi\Data\Link;
use Czim\JsonApi\Data\Meta;
use Czim\JsonApi\Data\Resource;
use Czim\JsonApi\Test\Helpers\Data\TestData;
use Czim\JsonApi\Test\TestCase;
use UnexpectedValueException;

/**
 * Class AbstractDataTest
 *
 * @group data
 */
class AbstractDataTest extends TestCase
{

    /**
     * @test
     */
    function it_decorates_an_attribute_as_a_data_object()
    {
        $data = new TestData;

        static::assertNull($data->meta);

        $data->meta = ['test' => 'value'];

        static::assertInstanceOf(Meta::class, $data->meta);
    }

    /**
     * @test
     */
    function it_forces_decorating_an_attribute_as_a_data_object()
    {
        $data = new TestData;

        static::assertInstanceOf(Link::class, $data->link);

        $data->link = ['self' => 'value'];

        static::assertInstanceOf(Link::class, $data->link);
    }

    /**
     * @test
     */
    function it_decorates_an_array_of_attributes_as_data_objects()
    {
        $data = new TestData;

        static::assertNull($data->resources);

        $data->resources = [ ['type' => 'value'] ];

        static::assertInstanceOf(Resource::class, head($data->resources));

        $data->resources = [ null ];

        static::assertNull(head($data->resources));
    }

    /**
     * @test
     */
    function it_throws_an_exception_if_a_value_to_decorate_is_not_an_array()
    {
        $this->expectException(UnexpectedValueException::class);

        $data = new TestData;

        static::assertNull($data->meta);

        $data->meta = 'string value';

        $data->meta;
    }

}
