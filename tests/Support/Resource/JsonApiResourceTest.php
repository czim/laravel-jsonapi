<?php
namespace Czim\JsonApi\Test\Support\Resource;

use Czim\JsonApi\Test\Helpers\Resources\AbstractTest\TestAbstractResource;
use Czim\JsonApi\Test\Helpers\Resources\AbstractTest\TestResourceWithAllReferences;
use Czim\JsonApi\Test\Helpers\Resources\AbstractTest\TestResourceWithBlacklistedReferences;
use Czim\JsonApi\Test\Helpers\Resources\AbstractTest\TestResourceWithNoReferences;
use Czim\JsonApi\Test\TestCase;

/**
 * Class JsonApiResourceTest
 *
 * @group resource
 */
class JsonApiResourceTest extends TestCase
{

    /**
     * @test
     */
    function it_returns_available_attributes()
    {
        $resource = new TestAbstractResource;

        static::assertEquals(
            ['name', 'title', 'accessor'],
            $resource->availableAttributes()
        );
    }

    /**
     * @test
     */
    function it_returns_available_includes()
    {
        $resource = new TestAbstractResource;

        static::assertEquals(
            ['comments', 'alternative-key', 'not-a-relation', 'method-does-not-exist'],
            $resource->availableIncludes()
        );
    }

    /**
     * @test
     */
    function it_returns_default_includes()
    {
        $resource = new TestAbstractResource;

        static::assertEquals(
            ['comments'],
            $resource->defaultIncludes()
        );
    }

    /**
     * @test
     */
    function it_returns_whether_to_include_references_for_an_include_key()
    {
        // For an explicit whitelist
        $resource = new TestAbstractResource;
        static::assertFalse($resource->includeReferencesForRelation('unknown-key'));
        static::assertTrue($resource->includeReferencesForRelation('comments'));
        static::assertFalse($resource->includeReferencesForRelation('alternative-key'));

        // For an implicit whitelist
        $resource = new TestResourceWithAllReferences;
        static::assertTrue($resource->includeReferencesForRelation('comments'));
        static::assertTrue($resource->includeReferencesForRelation('post'));

        // For an explicit blacklist
        $resource = new TestResourceWithBlacklistedReferences;
        static::assertFalse($resource->includeReferencesForRelation('comments'));
        static::assertTrue($resource->includeReferencesForRelation('post'));

        // For an implicit blacklist
        $resource = new TestResourceWithNoReferences;
        static::assertFalse($resource->includeReferencesForRelation('comments'));
        static::assertFalse($resource->includeReferencesForRelation('post'));
    }

    /**
     * @test
     */
    function it_returns_available_filters()
    {
        $resource = new TestAbstractResource;

        static::assertEquals(
            ['some-filter', 'test'],
            $resource->availableFilters()
        );
    }

    /**
     * @test
     */
    function it_returns_default_filters()
    {
        $resource = new TestAbstractResource;

        static::assertEquals(
            ['some-filter' => 13],
            $resource->defaultFilters()
        );
    }

    /**
     * @test
     */
    function it_returns_available_sort_attributes()
    {
        $resource = new TestAbstractResource;

        static::assertEquals(
            ['title', 'id'],
            $resource->availableSortAttributes()
        );
    }

    /**
     * @test
     */
    function it_returns_default_sort_attributes()
    {
        $resource = new TestAbstractResource;

        static::assertEquals(
            ['-id'],
            $resource->defaultSortAttributes()
        );
    }

}
