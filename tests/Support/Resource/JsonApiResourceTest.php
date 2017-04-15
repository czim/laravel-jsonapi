<?php
namespace Czim\JsonApi\Test\Support\Resource;

use Czim\JsonApi\Contracts\Support\Type\TypeMakerInterface;
use Czim\JsonApi\Test\Helpers\Models\TestPost;
use Czim\JsonApi\Test\Helpers\Models\TestSimpleModel;
use Czim\JsonApi\Test\Helpers\Resources\AbstractTest\TestAbstractResource;
use Czim\JsonApi\Test\Helpers\Resources\AbstractTest\TestResourceWithAllReferences;
use Czim\JsonApi\Test\Helpers\Resources\AbstractTest\TestResourceWithBlacklistedReferences;
use Czim\JsonApi\Test\Helpers\Resources\AbstractTest\TestResourceWithNoReferences;
use Czim\JsonApi\Test\TestCase;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mockery;

class JsonApiResourceTest extends TestCase
{

    /**
     * @test
     */
    function it_sets_and_returns_a_model_instance()
    {
        $resource = new TestAbstractResource;

        static::assertNull($resource->getModel());

        $model = new TestSimpleModel;

        static::assertSame($resource, $resource->setModel($model));

        static::assertSame($model, $resource->getModel());
    }

    /**
     * @test
     */
    function it_uses_the_typemaker_to_determine_the_type_for_its_model()
    {
        $resource = new TestAbstractResource;
        $resource->setModel(new TestSimpleModel);

        /** @var TypeMakerInterface|Mockery\Mock */
        $mockMaker = Mockery::mock(TypeMakerInterface::class);

        $mockMaker->shouldReceive('makeForModel')
            ->with(Mockery::type(TestSimpleModel::class))
            ->andReturn('test-simple-models');

        static::assertEquals('test-simple-models', $resource->type());
    }

    /**
     * @test
     */
    function it_returns_the_id()
    {
        $resource = new TestAbstractResource;

        $model = new TestSimpleModel;
        $model->id = 13;

        $resource->setModel($model);

        static::assertEquals('13', $resource->id());
    }

    /**
     * @test
     */
    function it_returns_a_model_attribute_value()
    {
        $resource = new TestAbstractResource;

        $model = new TestSimpleModel;
        $model->title = 'testing';

        $resource->setModel($model);

        static::assertEquals('testing', $resource->attributeValue('title'));
    }

    /**
     * @test
     */
    function it_returns_a_custom_accessor_attribute_value()
    {
        $resource = new TestAbstractResource;

        $resource->setModel(new TestSimpleModel);

        static::assertEquals('custom', $resource->attributeValue('accessor'));
    }

    /**
     * @test
     */
    function it_returns_a_default_value_if_model_attribute_value_is_null()
    {
        $resource = new TestAbstractResource;

        $model = new TestSimpleModel;
        $model->title = null;

        $resource->setModel($model);

        static::assertEquals('testing', $resource->attributeValue('title', 'testing'));
    }

    /**
     * @test
     */
    function it_returns_the_relation_method_name_for_an_include_key()
    {
        $resource = new TestAbstractResource;

        $resource->setModel(new TestPost);

        static::assertEquals('comments', $resource->getRelationMethodForInclude('comments'));
    }

    /**
     * @test
     */
    function it_returns_the_relation_method_name_for_an_include_key_by_alias()
    {
        $resource = new TestAbstractResource;

        $resource->setModel(new TestPost);

        static::assertEquals('comments', $resource->getRelationMethodForInclude('alternative-key'));
    }

    /**
     * @test
     * @expectedException \Czim\JsonApi\Exceptions\InvalidIncludeException
     */
    function it_throws_an_exception_if_the_relation_method_name_is_requested_for_an_unknown_include_key()
    {
        $resource = new TestAbstractResource;

        $resource->setModel(new TestPost);

        $resource->getRelationMethodForInclude('unknown-key');
    }

    /**
     * @test
     */
    function it_returns_the_relation_method_instance_of_the_model_for_an_include_key()
    {
        $resource = new TestAbstractResource;

        $resource->setModel(new TestPost);

        static::assertInstanceOf(HasMany::class, $resource->includeRelation('comments'));
    }

    /**
     * @test
     */
    function it_returns_the_relation_method_instance_of_the_model_for_an_include_key_by_alias()
    {
        $resource = new TestAbstractResource;

        $resource->setModel(new TestPost);

        static::assertInstanceOf(HasMany::class, $resource->includeRelation('alternative-key'));
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    function it_throws_an_exception_if_an_include_relation_method_does_not_exist()
    {
        $resource = new TestAbstractResource;

        $resource->setModel(new TestPost);

        $resource->includeRelation('method-does-not-exist');
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    function it_throws_an_exception_if_an_include_relation_method_is_not_an_eloquent_relation()
    {
        $resource = new TestAbstractResource;

        $resource->setModel(new TestPost);

        $resource->includeRelation('not-a-relation');
    }

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
