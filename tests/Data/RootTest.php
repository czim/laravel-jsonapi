<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\JsonApi\Test\Data;

use Czim\JsonApi\Data\Root;
use Czim\JsonApi\Enums\RootType;
use Czim\JsonApi\Test\TestCase;

/**
 * Class RootTest
 *
 * @group data
 */
class RootTest extends TestCase
{

    /**
     * @test
     */
    function it_returns_the_root_type()
    {
        $data = new Root([
            'data' => [
                'type' => 'test',
                'id'   => '1',
            ],
        ]);

        static::assertEquals(RootType::RESOURCE, $data->getRootType());

        $data = new Root([
            'errors' => [
                [
                    'detail' => 'Testing',
                ]
            ],
        ]);

        static::assertEquals(RootType::ERROR, $data->getRootType());

        $data = new Root([
            'meta' => [],
        ]);

        static::assertEquals(RootType::META, $data->getRootType());

        $data = new Root;

        static::assertEquals(RootType::UNKNOWN, $data->getRootType());
    }

    /**
     * @test
     */
    function it_returns_whether_data_key_is_set()
    {
        $data = new Root;

        static::assertFalse($data->hasData());

        $data = new Root(['data' => []]);

        static::assertTrue($data->hasData());
    }

    /**
     * @test
     */
    function it_returns_whether_errors_key_is_set()
    {
        $data = new Root;

        static::assertFalse($data->hasErrors());

        $data = new Root(['errors' => []]);

        static::assertTrue($data->hasErrors());
    }

    /**
     * @test
     */
    function it_returns_whether_included_key_is_set()
    {
        $data = new Root;

        static::assertFalse($data->hasIncluded());

        $data = new Root(['included' => []]);

        static::assertTrue($data->hasIncluded());
    }

    /**
     * @test
     */
    function it_returns_whether_jsonapi_key_is_set()
    {
        $data = new Root;

        static::assertFalse($data->hasJsonApi());

        $data = new Root(['jsonapi' => []]);

        static::assertTrue($data->hasJsonApi());
    }

    /**
     * @test
     */
    function it_returns_whether_links_key_is_set()
    {
        $data = new Root;

        static::assertFalse($data->hasLinks());

        $data = new Root(['links' => []]);

        static::assertTrue($data->hasLinks());
    }

    /**
     * @test
     */
    function it_returns_whether_meta_key_is_set()
    {
        $data = new Root;

        static::assertFalse($data->hasMeta());

        $data = new Root(['meta' => []]);

        static::assertTrue($data->hasMeta());
    }

    /**
     * @test
     */
    function it_returns_whether_data_is_explicitly_null()
    {
        $data = new Root;

        static::assertFalse($data->hasNullData());

        $data = new Root(['data' => null]);

        static::assertTrue($data->hasNullData());
    }

    /**
     * @test
     */
    function it_returns_whether_it_has_single_resource_data()
    {
        $data = new Root;

        static::assertFalse($data->hasSingleResourceData());

        $data = new Root(['data' => ['type' => 'test', 'id' => '1']]);

        static::assertTrue($data->hasSingleResourceData());
        // Load it again to check when already eager loaded
        static::assertTrue($data->hasSingleResourceData());

        $data['data'] = [ ['type' => 'test', 'id' => '1'] ];

        static::assertFalse($data->hasSingleResourceData());
    }

    /**
     * @test
     */
    function it_returns_whether_it_has_multiple_resource_data()
    {
        $data = new Root;

        static::assertFalse($data->hasMultipleResourceData());

        $data = new Root(['data' => ['type' => 'test', 'id' => '1']]);

        static::assertFalse($data->hasMultipleResourceData());

        $data['data'] = [ ['type' => 'test', 'id' => '1'] ];

        static::assertTrue($data->hasMultipleResourceData());
    }

}
