<?php
namespace Czim\JsonApi\Test\Data;

use Czim\JsonApi\Data\Link;
use Czim\JsonApi\Data\Links;
use Czim\JsonApi\Test\TestCase;

/**
 * Class LinksTest
 *
 * @group data
 */
class LinksTest extends TestCase
{

    /**
     * @test
     */
    function it_decorates_links_as_string_or_object()
    {
        $data = new Links([
            'self'    => 'http://link',
            'related' => [
                'href' => 'http://another',
                'meta' => [],
            ],
            'empty' => null,
        ]);

        static::assertEquals('http://link', $data->self);
        static::assertInstanceOf(Link::class, $data->related);
        static::assertEquals('http://another', $data->related->href);
        static::assertNull($data->empty);
    }

}
