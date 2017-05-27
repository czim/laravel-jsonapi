<?php
namespace Czim\JsonApi\Test\Support\Resource;

use Czim\JsonApi\Contracts\Support\Type\TypeMakerInterface;
use Czim\JsonApi\Test\Helpers\Models\TestPost;
use Czim\JsonApi\Test\Helpers\Models\TestSimpleModel;
use Czim\JsonApi\Test\Helpers\Resources\AbstractTest\TestAbstractEloquentResource;
use Czim\JsonApi\Test\Helpers\Resources\AbstractTest\TestResourceWithAbsoluteUrl;
use Czim\JsonApi\Test\Helpers\Resources\AbstractTest\TestResourceWithRelativeUrl;
use Czim\JsonApi\Test\TestCase;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mockery;

/**
 * Class EloquentResourceTest
 *
 * @group resource
 */
class EloquentResourceTest extends TestCase
{

    /**
     * @test
     */
    function it_sets_and_returns_a_model_instance()
    {
        $resource = new TestAbstractEloquentResource;

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
        $resource = new TestAbstractEloquentResource;
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
        $resource = new TestAbstractEloquentResource;

        $model = new TestSimpleModel;
        $model->id = 13;

        $resource->setModel($model);

        static::assertEquals('13', $resource->id());
    }

    /**
     * @test
     */
    function it_returns_the_url()
    {
        $resource = new TestAbstractEloquentResource;
        $resource->setModel(new TestSimpleModel);

        static::assertEquals(
            'http://localhost/api/test-simple-models',
            $resource->url()
        );

        $this->app['config']->set('jsonapi.base_url', 'https://base_url/api');

        // With a set relative path
        static::assertEquals(
            'https://base_url/api/test/resource-path',
            (new TestResourceWithRelativeUrl())->url()
        );

        // With a set absolute path
        static::assertEquals(
            'https://localhost/v1/test/resource-path',
            (new TestResourceWithAbsoluteUrl())->url()
        );
    }

    /**
     * @test
     */
    function it_returns_a_model_attribute_value()
    {
        $resource = new TestAbstractEloquentResource;

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
        $resource = new TestAbstractEloquentResource;

        $resource->setModel(new TestSimpleModel);

        static::assertEquals('custom', $resource->attributeValue('accessor'));
    }

    /**
     * @test
     */
    function it_returns_a_default_value_if_model_attribute_value_is_null()
    {
        $resource = new TestAbstractEloquentResource;

        $model = new TestSimpleModel;
        $model->title = null;

        $resource->setModel($model);

        static::assertEquals('testing', $resource->attributeValue('title', 'testing'));
    }

    /**
     * @test
     */
    function it_returns_a_datetime_value_formatted_by_a_default_format()
    {
        $resource = new TestAbstractEloquentResource;

        $model = new TestSimpleModel;
        $model->created_at = '2017-01-01 01:02:03';

        $resource->setModel($model);

        static::assertEquals('2017-01-01T01:02:03+00:00', $resource->attributeValue('created_at'));
    }

    /**
     * @test
     */
    function it_returns_a_datetime_value_formatted_by_a_configured_format()
    {
        $resource = new TestAbstractEloquentResource;

        $model = new TestSimpleModel;
        $model->updated_at = '2017-01-01 01:02:03';

        $resource->setModel($model);

        static::assertEquals('2017-01-01 01:02', $resource->attributeValue('updated_at'));
    }

    /**
     * @test
     */
    function it_returns_a_custom_value_as_a_datetime_if_it_is_listed_as_a_datetime_attribute()
    {
        $resource = new TestAbstractEloquentResource;

        $model = new TestSimpleModel;

        $resource->setModel($model);

        static::assertEquals('2017-01-02', $resource->attributeValue('date_accessor'));
    }

    /**
     * @test
     */
    function it_returns_the_relation_method_name_for_an_include_key()
    {
        $resource = new TestAbstractEloquentResource;

        $resource->setModel(new TestPost);

        static::assertEquals('comments', $resource->getRelationMethodForInclude('comments'));
    }

    /**
     * @test
     */
    function it_returns_the_relation_method_name_for_an_include_key_by_alias()
    {
        $resource = new TestAbstractEloquentResource;

        $resource->setModel(new TestPost);

        static::assertEquals('comments', $resource->getRelationMethodForInclude('alternative-key'));
    }

    /**
     * @test
     * @expectedException \Czim\JsonApi\Exceptions\InvalidIncludeException
     */
    function it_throws_an_exception_if_the_relation_method_name_is_requested_for_an_unknown_include_key()
    {
        $resource = new TestAbstractEloquentResource;

        $resource->setModel(new TestPost);

        $resource->getRelationMethodForInclude('unknown-key');
    }

    /**
     * @test
     */
    function it_returns_the_relation_method_instance_of_the_model_for_an_include_key()
    {
        $resource = new TestAbstractEloquentResource;

        $resource->setModel(new TestPost);

        static::assertInstanceOf(HasMany::class, $resource->includeRelation('comments'));
    }

    /**
     * @test
     */
    function it_returns_the_relation_method_instance_of_the_model_for_an_include_key_by_alias()
    {
        $resource = new TestAbstractEloquentResource;

        $resource->setModel(new TestPost);

        static::assertInstanceOf(HasMany::class, $resource->includeRelation('alternative-key'));
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    function it_throws_an_exception_if_an_include_relation_method_does_not_exist()
    {
        $resource = new TestAbstractEloquentResource;

        $resource->setModel(new TestPost);

        $resource->includeRelation('method-does-not-exist');
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    function it_throws_an_exception_if_an_include_relation_method_is_not_an_eloquent_relation()
    {
        $resource = new TestAbstractEloquentResource;

        $resource->setModel(new TestPost);

        $resource->includeRelation('not-a-relation');
    }

}
