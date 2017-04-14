<?php
namespace Czim\JsonApi\Test\Integration\Encoding;

use Czim\JsonApi\Contracts\Repositories\ResourceCollectorInterface;
use Czim\JsonApi\Encoder\Encoder;
use Czim\JsonApi\Encoder\Factories\TransformerFactory;
use Czim\JsonApi\Encoder\Transformers\ModelTransformer;
use Czim\JsonApi\Repositories\ResourceRepository;
use Czim\JsonApi\Test\AbstractSeededTestCase;
use Czim\JsonApi\Test\Helpers\Models\TestRelatedModel;
use Czim\JsonApi\Test\Helpers\Models\TestSimpleModel;
use Czim\JsonApi\Test\Helpers\Resources\TestRelatedModelResource;
use Czim\JsonApi\Test\Helpers\Resources\TestSimpleModelWithRelationsResource;
use Illuminate\Support\Collection;
use Mockery;

class ModelEncodingTest extends AbstractSeededTestCase
{

    /**
     * @test
     */
    function it_transforms_a_model_with_related_records()
    {
        /** @var ResourceCollectorInterface|Mockery\Mock $collector */
        $collector = Mockery::mock(ResourceCollectorInterface::class);
        $collector->shouldReceive('collect')->andReturn(new Collection);

        $factory    = new TransformerFactory;
        $repository = new ResourceRepository($collector);
        $encoder    = new Encoder($factory, $repository);

        $repository->register(TestSimpleModel::class, new TestSimpleModelWithRelationsResource);
        $repository->register(TestRelatedModel::class, new TestRelatedModelResource);
        
        $transformer = new ModelTransformer;
        $transformer->setEncoder($encoder);

        static::assertEquals(
            [
                'data' => [
                    'id'         => '1',
                    'type'       => 'test-simple-models',
                    'attributes' => [
                        'name'            => 'Test A',
                        'active'          => true,
                        'simple-appended' => 'testing',
                    ],
                    'relationships' => [
                        'children' => [
                            'links' => [
                                'self'    => '/api/test-simple-models/relationships/children',
                                'related' => '/api/test-simple-models/test-related-models',
                            ],
                            'data' => [
                                ['id' => '1', 'type' => 'test-related-models'],
                                ['id' => '2', 'type' => 'test-related-models'],
                                ['id' => '3', 'type' => 'test-related-models'],
                            ],
                        ],
                        'single-related' => [
                            'links' => [
                                'self'    => '/api/test-simple-models/relationships/single-related',
                                'related' => '/api/test-simple-models/test-related-models',
                            ],
                            'data' => null,
                        ],
                    ],
                ],
            ],
            $transformer->transform(TestSimpleModel::first())
        );
    }

    /**
     * @test
     */
    function it_transforms_a_model_with_singular_related_data()
    {
        /** @var ResourceCollectorInterface|Mockery\Mock $collector */
        $collector = Mockery::mock(ResourceCollectorInterface::class);
        $collector->shouldReceive('collect')->andReturn(new Collection);

        $factory    = new TransformerFactory;
        $repository = new ResourceRepository($collector);
        $encoder    = new Encoder($factory, $repository);

        $repository->register(TestSimpleModel::class, new TestSimpleModelWithRelationsResource);
        $repository->register(TestRelatedModel::class, new TestRelatedModelResource);

        $transformer = new ModelTransformer;
        $transformer->setEncoder($encoder);

        static::assertEquals(
            [
                'data' => [
                    'id'         => '2',
                    'type'       => 'test-simple-models',
                    'attributes' => [
                        'name'            => 'Test B',
                        'active'          => false,
                        'simple-appended' => 'testing',
                    ],
                    'relationships' => [
                        'children' => [
                            'links' => [
                                'self'    => '/api/test-simple-models/relationships/children',
                                'related' => '/api/test-simple-models/test-related-models',
                            ],
                            'data' => [],
                        ],
                        'single-related' => [
                            'links' => [
                                'self'    => '/api/test-simple-models/relationships/single-related',
                                'related' => '/api/test-simple-models/test-related-models',
                            ],
                            'data' => ['type' => 'test-related-models', 'id' => '1'],
                        ],
                    ],
                ],
            ],
            $transformer->transform(TestSimpleModel::skip(1)->first())
        );
    }

    /**
     * @test
     */
    function it_transforms_a_model_with_eager_loaded_data()
    {
        $model = TestSimpleModel::first();
        $model->test_related_model_id = 2;
        $model->save();

        $model->load('children', 'related');


        /** @var ResourceCollectorInterface|Mockery\Mock $collector */
        $collector = Mockery::mock(ResourceCollectorInterface::class);
        $collector->shouldReceive('collect')->andReturn(new Collection);

        $factory    = new TransformerFactory;
        $repository = new ResourceRepository($collector);
        $encoder    = new Encoder($factory, $repository);

        $repository->register(TestSimpleModel::class, new TestSimpleModelWithRelationsResource);
        $repository->register(TestRelatedModel::class, new TestRelatedModelResource);

        $transformer = new ModelTransformer;
        $transformer->setEncoder($encoder);

        static::assertEquals(
            [
                'data' => [
                    'id'         => '1',
                    'type'       => 'test-simple-models',
                    'attributes' => [
                        'name'            => 'Test A',
                        'active'          => true,
                        'simple-appended' => 'testing',
                    ],
                    'relationships' => [
                        'children' => [
                            'links' => [
                                'self'    => '/api/test-simple-models/relationships/children',
                                'related' => '/api/test-simple-models/test-related-models',
                            ],
                            'data' => [
                                ['id' => '1', 'type' => 'test-related-models'],
                                ['id' => '2', 'type' => 'test-related-models'],
                                ['id' => '3', 'type' => 'test-related-models'],
                            ],
                        ],
                        'single-related' => [
                            'links' => [
                                'self'    => '/api/test-simple-models/relationships/single-related',
                                'related' => '/api/test-simple-models/test-related-models',
                            ],
                            'data' => ['type' => 'test-related-models', 'id' => '1'],
                        ],
                    ],
                ],
            ],
            $transformer->transform($model)
        );
    }

}
