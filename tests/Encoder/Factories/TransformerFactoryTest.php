<?php
namespace Czim\JsonApi\Test\Encoder\Factories;

use Czim\JsonApi\Encoder\Factories\TransformerFactory;
use Czim\JsonApi\Encoder\Transformers\ExceptionTransformer;
use Czim\JsonApi\Encoder\Transformers\ModelCollectionTransformer;
use Czim\JsonApi\Encoder\Transformers\ModelTransformer;
use Czim\JsonApi\Encoder\Transformers\PaginatedModelsTransformer;
use Czim\JsonApi\Encoder\Transformers\SimpleTransformer;
use Czim\JsonApi\Test\Helpers\Models\TestSimpleModel;
use Czim\JsonApi\Test\TestCase;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Class TransformerFactoryTest
 *
 * @group encoding
 */
class TransformerFactoryTest extends TestCase
{

    /**
     * @test
     */
    function it_makes_a_model_transformer_for_a_model_instance()
    {
        $factory = new TransformerFactory;

        static::assertInstanceOf(ModelTransformer::class, $factory->makeFor(new TestSimpleModel));
    }

    /**
     * @test
     */
    function it_makes_a_model_collection_transformer_for_a_model_collection()
    {
        $factory = new TransformerFactory;

        static::assertInstanceOf(ModelCollectionTransformer::class, $factory->makeFor(new EloquentCollection));
        static::assertInstanceOf(ModelCollectionTransformer::class, $factory->makeFor(new Collection([
            new TestSimpleModel
        ])));
    }

    /**
     * @test
     */
    function it_makes_an_exception_transformer_for_an_exception()
    {
        $factory = new TransformerFactory;

        static::assertInstanceOf(ExceptionTransformer::class, $factory->makeFor(new \Exception('test')));
    }

    /**
     * @test
     */
    function it_makes_a_paginator_transformer_for_a_paginated_collection_of_models()
    {
        $factory = new TransformerFactory;

        $collection = new LengthAwarePaginator([new TestSimpleModel], 1, 1);

        static::assertInstanceOf(PaginatedModelsTransformer::class, $factory->makeFor($collection));

        $collection = new LengthAwarePaginator(new EloquentCollection, 1, 1);

        static::assertInstanceOf(PaginatedModelsTransformer::class, $factory->makeFor($collection));
    }

    /**
     * @test
     */
    function it_defaults_to_a_simple_transformer()
    {
        $factory = new TransformerFactory;

        static::assertInstanceOf(SimpleTransformer::class, $factory->makeFor($this));
    }

    /**
     * @test
     */
    function it_makes_a_custom_transformer_for_a_mapped_object()
    {
        $this->app['config']->set('jsonapi.transform.map', [ static::class => ExceptionTransformer::class ]);

        $factory = new TransformerFactory;

        static::assertInstanceOf(ExceptionTransformer::class, $factory->makeFor($this));
    }

}
