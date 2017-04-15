<?php
namespace Czim\JsonApi\Test\Encoder\Transformers;

use Czim\JsonApi\Contracts\Encoder\EncoderInterface;
use Czim\JsonApi\Encoder\Transformers\ModelCollectionTransformer;
use Czim\JsonApi\Test\Helpers\Models\TestAlternativeModel;
use Czim\JsonApi\Test\Helpers\Models\TestSimpleModel;
use Czim\JsonApi\Test\Helpers\Resources\TestAlternativeModelResource;
use Czim\JsonApi\Test\Helpers\Resources\TestSimpleModelResource;
use Czim\JsonApi\Test\TestCase;
use Illuminate\Support\Collection;
use Mockery;

/**
 * Class ModelCollectionTransformerTest
 *
 * @group encoding
 */
class ModelCollectionTransformerTest extends TestCase
{

    /**
     * @test
     */
    function it_transforms_a_collection_of_simple_models_using_a_resource()
    {
        // Prepare the collection
        $collection = new Collection;

        $model = new TestSimpleModel;
        $model->id           = 13;
        $model->unique_field = 'test123';
        $model->second_field = 'test';
        $model->name         = 'Testing!';
        $model->active       = false;

        $collection->push($model);

        $model = new TestSimpleModel;
        $model->id           = 14;
        $model->unique_field = 'test124';
        $model->second_field = 'test2';
        $model->name         = 'Testing?';
        $model->active       = true;

        $collection->push($model);

        // Prepare dependencies & mocks
        $encoderMock = $this->getMockEncoder();
        $encoderMock->shouldReceive('getResourceForModel')->with(Mockery::type(TestSimpleModel::class))
            ->andReturn(new TestSimpleModelResource);

        $transformer = new ModelCollectionTransformer;
        $transformer->setEncoder($encoderMock);
        $transformer->setIsVariable(false);

        static::assertEquals(
            [
                'data' => [
                    [
                        'id'         => '13',
                        'type'       => 'test-simple-models',
                        'attributes' => [
                            'unique-field' => 'test123',
                            'second-field' => 'test',
                            'name'         => 'Testing!',
                            'active'       => false,
                        ],
                    ],
                    [
                        'id'         => '14',
                        'type'       => 'test-simple-models',
                        'attributes' => [
                            'unique-field' => 'test124',
                            'second-field' => 'test2',
                            'name'         => 'Testing?',
                            'active'       => true,
                        ],
                    ],
                ],
            ],
            $transformer->transform($collection)
        );
    }

    /**
     * @test
     */
    function it_transforms_an_empty_collection()
    {
        // Prepare the collection
        $collection = new Collection;

        // Prepare dependencies & mocks
        $encoderMock = $this->getMockEncoder();

        $transformer = new ModelCollectionTransformer;
        $transformer->setEncoder($encoderMock);

        static::assertEquals(
            [
                'data' => [],
            ],
            $transformer->transform($collection)
        );
    }

    /**
     * @test
     */
    function it_transforms_a_collection_of_varying_models_using_their_relevant_resources()
    {
        // Prepare the collection
        $collection = new Collection;

        $model = new TestSimpleModel;
        $model->id           = 13;
        $model->unique_field = 'test123';
        $model->second_field = 'test';
        $model->name         = 'Testing!';
        $model->active       = false;

        $collection->push($model);

        $model = new TestAlternativeModel;
        $model->id    = 8;
        $model->slug  = 'test-slug';
        $model->value = 3.5;

        $collection->push($model);

        // Prepare dependencies & mocks
        $encoderMock = $this->getMockEncoder();
        $encoderMock->shouldReceive('getResourceForModel')->with(Mockery::type(TestSimpleModel::class))
            ->andReturn(new TestSimpleModelResource);
        $encoderMock->shouldReceive('getResourceForModel')->with(Mockery::type(TestAlternativeModel::class))
            ->andReturn(new TestAlternativeModelResource);

        $transformer = new ModelCollectionTransformer;
        $transformer->setEncoder($encoderMock);
        $transformer->setIsVariable(true);

        static::assertEquals(
            [
                'data' => [
                    [
                        'id'         => '13',
                        'type'       => 'test-simple-models',
                        'attributes' => [
                            'unique-field' => 'test123',
                            'second-field' => 'test',
                            'name'         => 'Testing!',
                            'active'       => false,
                        ],
                    ],
                    [
                        'id'         => '8',
                        'type'       => 'test-alternative-models',
                        'attributes' => [
                            'slug'  => 'test-slug',
                            'value' => 3.5,
                        ],
                    ],
                ],
            ],
            $transformer->transform($collection)
        );
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    function it_throws_an_exception_if_data_is_not_a_model_collection()
    {
        $transformer = new ModelCollectionTransformer;
        $transformer->setEncoder($this->getMockEncoder());

        $transformer->transform($this);
    }

    /**
     * @return EncoderInterface|Mockery\MockInterface
     */
    protected function getMockEncoder()
    {
        return Mockery::mock(EncoderInterface::class);
    }

}
