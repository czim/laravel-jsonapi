<?php
namespace Czim\JsonApi\Test\Encoder\Transformers;

use Czim\JsonApi\Contracts\Encoder\EncoderInterface;
use Czim\JsonApi\Encoder\Transformers\PaginatedModelsTransformer;
use Czim\JsonApi\Test\Helpers\Models\TestSimpleModel;
use Czim\JsonApi\Test\Helpers\Resources\TestSimpleModelResource;
use Czim\JsonApi\Test\TestCase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Mockery;

/**
 * Class PaginatedModelsTransformerTest
 *
 * @group encoding
 */
class PaginatedModelsTransformerTest extends TestCase
{

    /**
     * @test
     */
    function it_transforms_a_paginated_collection_of_simple_models()
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

        $paginator = new LengthAwarePaginator($collection, 8, 2, 2);

        // Prepare dependencies & mocks
        $encoderMock = $this->getMockEncoder();
        $encoderMock->shouldReceive('getTopResourceUrl')->andReturn('http://localhost/models');
        $encoderMock->shouldReceive('getResourceForModel')->with(Mockery::type(TestSimpleModel::class))
            ->andReturn(new TestSimpleModelResource);

        $encoderMock->shouldReceive('setLink')->with('first', 'http://localhost/models?page[number]=1')
            ->once()->andReturnSelf();
        $encoderMock->shouldReceive('setLink')->with('next', 'http://localhost/models?page[number]=3')
            ->once()->andReturnSelf();
        $encoderMock->shouldReceive('setLink')->with('prev', 'http://localhost/models?page[number]=1')
            ->once()->andReturnSelf();
        $encoderMock->shouldReceive('setLink')->with('last', 'http://localhost/models?page[number]=4')
            ->once()->andReturnSelf();


        $transformer = new PaginatedModelsTransformer;
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
            $transformer->transform($paginator)
        );
    }

    /**
     * @test
     */
    function it_defaults_to_using_paginator_defined_links_if_resource_url_is_not_defined_in_encoder()
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

        $paginator = new LengthAwarePaginator($collection, 8, 2, 2, [
            'path'     => '/testing/path',
            'pageName' => 'pg_test',
        ]);

        // Prepare dependencies & mocks
        $encoderMock = $this->getMockEncoder();
        $encoderMock->shouldReceive('getTopResourceUrl')->andReturn(null);
        $encoderMock->shouldReceive('getResourceForModel')->with(Mockery::type(TestSimpleModel::class))
            ->andReturn(new TestSimpleModelResource);

        $encoderMock->shouldReceive('setLink')->with('first', '/testing/path?pg_test=1')->once()->andReturnSelf();
        $encoderMock->shouldReceive('setLink')->with('next', '/testing/path?pg_test=3')->once()->andReturnSelf();
        $encoderMock->shouldReceive('setLink')->with('prev', '/testing/path?pg_test=1')->once()->andReturnSelf();
        $encoderMock->shouldReceive('setLink')->with('last', '/testing/path?pg_test=4')->once()->andReturnSelf();

        $transformer = new PaginatedModelsTransformer;
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
            $transformer->transform($paginator)
        );
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    function it_throws_an_exception_if_data_is_not_a_paginator()
    {
        $transformer = new PaginatedModelsTransformer;
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
