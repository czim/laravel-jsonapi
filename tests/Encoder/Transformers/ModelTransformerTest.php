<?php
namespace Czim\JsonApi\Test\Encoder\Transformers;

use Czim\JsonApi\Contracts\Encoder\EncoderInterface;
use Czim\JsonApi\Encoder\Transformers\ModelTransformer;
use Czim\JsonApi\Test\Helpers\Models\TestSimpleModel;
use Czim\JsonApi\Test\Helpers\Resources\TestSimpleModelResource;
use Czim\JsonApi\Test\Helpers\Resources\TestSimpleModelWithoutAttributesResource;
use Czim\JsonApi\Test\TestCase;
use Mockery;

class ModelTransformerTest extends TestCase
{

    /**
     * @test
     */
    function it_transforms_a_simple_model_using_a_resource()
    {
        $model = new TestSimpleModel;

        $model->id           = 13;
        $model->unique_field = 'test123';
        $model->second_field = 'test';
        $model->name         = 'Testing!';
        $model->active       = false;

        $resource = new TestSimpleModelResource;
        $resource->setModel($model);

        $encoderMock = $this->getMockEncoder();
        $encoderMock->shouldReceive('getResourceForModel')->with($model)->andReturn($resource);

        $transformer = new ModelTransformer;
        $transformer->setEncoder($encoderMock);

        static::assertEquals(
            [
                'data' => [
                    'id'         => '13',
                    'type'       => 'test-simple-models',
                    'attributes' => [
                        'unique-field' => 'test123',
                        'second-field' => 'test',
                        'name'         => 'Testing!',
                        'active'       => false,
                    ],
                ],
            ],
            $transformer->transform($model)
        );
    }

    /**
     * @test
     */
    function it_transforms_a_simple_model_using_a_resource_without_attributes()
    {
        $model = new TestSimpleModel;

        $model->id           = 13;
        $model->unique_field = 'test123';

        $resource = new TestSimpleModelWithoutAttributesResource;
        $resource->setModel($model);

        $encoderMock = $this->getMockEncoder();
        $encoderMock->shouldReceive('getResourceForModel')->with($model)->andReturn($resource);

        $transformer = new ModelTransformer;
        $transformer->setEncoder($encoderMock);

        static::assertEquals(
            [
                'data' => [
                    'id'   => '13',
                    'type' => 'test-simple-models',
                ],
            ],
            $transformer->transform($model)
        );
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    function it_throws_an_exception_if_data_is_not_a_model_instance()
    {
        $transformer = new ModelTransformer;
        $transformer->setEncoder($this->getMockEncoder());

        $transformer->transform($this);
    }

    /**
     * @test
     * @expectedException \Czim\JsonApi\Exceptions\EncodingException
     */
    function it_throws_an_exception_if_no_resource_is_registered_for_the_model()
    {
        $model = new TestSimpleModel;

        $encoderMock = $this->getMockEncoder();
        $encoderMock->shouldReceive('getResourceForModel')->with($model)->andReturn(false);

        $transformer = new ModelTransformer;
        $transformer->setEncoder($encoderMock);

        $transformer->transform($model);
    }

    /**
     * @return EncoderInterface|Mockery\MockInterface
     */
    protected function getMockEncoder()
    {
        return Mockery::mock(EncoderInterface::class);
    }

}
