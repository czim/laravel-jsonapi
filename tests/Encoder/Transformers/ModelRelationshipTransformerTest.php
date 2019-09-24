<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\JsonApi\Test\Encoder\Transformers;

use Czim\JsonApi\Contracts\Encoder\EncoderInterface;
use Czim\JsonApi\Contracts\Resource\EloquentResourceInterface;
use Czim\JsonApi\Encoder\Transformers\ModelRelationshipTransformer;
use Czim\JsonApi\Exceptions\EncodingException;
use Czim\JsonApi\Support\Resource\RelationshipTransformData;
use Czim\JsonApi\Test\TestCase;
use InvalidArgumentException;
use Mockery;

/**
 * Class ModelRelationshipTransformerTest
 *
 * The happy path with sideloaded relation data is tested by integration tests.
 *
 * @group encoding
 */
class ModelRelationshipTransformerTest extends TestCase
{

    /**
     * @test
     */
    function it_transforms_relation_data_for_a_model_resource_without_references()
    {
        /** @var Mockery\Mock|EloquentResourceInterface $resource */
        $resource = Mockery::mock(EloquentResourceInterface::class);
        $resource->shouldReceive('availableIncludes')->andReturn(['test']);
        $resource->shouldReceive('url')->andReturn('http://test.url');
        $resource->shouldReceive('id')->andReturn('1');
        $resource->shouldReceive('type')->andReturn('tests');

        $data = new RelationshipTransformData([
            'resource'   => $resource,
            'include'    => 'test',
            'references' => false,
            'sideload'   => false,
        ]);

        $transformer = new ModelRelationshipTransformer;
        $transformer->setEncoder($this->getMockEncoder());

        static::assertEquals(
            [
                'links' => [
                    'self'    => 'http://test.url/1/relationships/test',
                    'related' => 'http://test.url/1/related/test',
                ]
            ],
            $transformer->transform($data)
        );
    }

    /**
     * @test
     */
    function it_transforms_relation_data_for_a_model_resource_without_links_if_configured()
    {
        /** @var Mockery\Mock|EloquentResourceInterface $resource */
        $resource = Mockery::mock(EloquentResourceInterface::class);
        $resource->shouldReceive('availableIncludes')->andReturn(['test']);

        $this->app['config']->set('jsonapi.transform.links.relationships', false);
        $this->app['config']->set('jsonapi.transform.links.related', false);

        $data = new RelationshipTransformData([
            'resource'   => $resource,
            'include'    => 'test',
            'references' => false,
            'sideload'   => false,
        ]);

        $transformer = new ModelRelationshipTransformer;
        $transformer->setEncoder($this->getMockEncoder());

        static::assertEquals([], $transformer->transform($data));
    }

    /**
     * @test
     */
    function it_transforms_relation_data_for_a_model_resource_with_references()
    {
        /** @var Mockery\Mock|EloquentResourceInterface $resource */
        $resource = Mockery::mock(EloquentResourceInterface::class);
        $resource->shouldReceive('availableIncludes')->andReturn(['test']);
        $resource->shouldReceive('url')->andReturn('http://test.url');
        $resource->shouldReceive('id')->andReturn('1');
        $resource->shouldReceive('type')->andReturn('tests');
        $resource->shouldReceive('relationshipReferences')->andReturn(['type' => 'tests', 'id' => '2']);

        $data = new RelationshipTransformData([
            'resource'   => $resource,
            'include'    => 'test',
            'references' => true,
            'sideload'   => false,
        ]);

        $transformer = new ModelRelationshipTransformer;
        $transformer->setEncoder($this->getMockEncoder());

        static::assertEquals(
            [
                'links' => [
                    'self'    => 'http://test.url/1/relationships/test',
                    'related' => 'http://test.url/1/related/test',
                ],
                'data' => [
                    'type' => 'tests',
                    'id'   => '2',
                ],
            ],
            $transformer->transform($data)
        );
    }

    /**
     * @test
     */
    function it_throws_an_exception_if_data_does_not_indicate_a_valid_include()
    {
        $this->expectException(EncodingException::class);

        /** @var Mockery\Mock|EloquentResourceInterface $resource */
        $resource = Mockery::mock(EloquentResourceInterface::class);
        $resource->shouldReceive('availableIncludes')->andReturn([]);
        $resource->shouldReceive('url')->andReturn('http://test.url');
        $resource->shouldReceive('id')->andReturn('1');
        $resource->shouldReceive('type')->andReturn('tests');

        $data = new RelationshipTransformData([
            'resource'   => $resource,
            'include'    => 'test',
            'references' => false,
            'sideload'   => true,
        ]);

        $transformer = new ModelRelationshipTransformer;
        $transformer->setEncoder($this->getMockEncoder());

        $transformer->transform($data);
    }

    /**
     * @test
     */
    function it_throws_an_exception_if_data_is_not_a_relationship_data_instance()
    {
        $this->expectException(InvalidArgumentException::class);

        $transformer = new ModelRelationshipTransformer;
        $transformer->setEncoder($this->getMockEncoder());

        $transformer->transform($this);
    }

    /**
     * @test
     */
    function it_throws_an_exception_if_no_resource_is_set_in_data()
    {
        $this->expectException(EncodingException::class);

        $transformer = new ModelRelationshipTransformer;
        $transformer->setEncoder($this->getMockEncoder());

        $transformer->transform(new RelationshipTransformData([]));
    }

    /**
     * @return EncoderInterface|Mockery\MockInterface
     */
    protected function getMockEncoder()
    {
        return Mockery::mock(EncoderInterface::class);
    }

}
