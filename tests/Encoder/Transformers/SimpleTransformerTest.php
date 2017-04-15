<?php
namespace Czim\JsonApi\Test\Encoder\Transformers;

use Czim\JsonApi\Contracts\Encoder\EncoderInterface;
use Czim\JsonApi\Encoder\Transformers\SimpleTransformer;
use Czim\JsonApi\Support\Resource\RelationData;
use Czim\JsonApi\Test\TestCase;
use Mockery;

/**
 * Class SimpleTransformerTest
 *
 * @group encoding
 */
class SimpleTransformerTest extends TestCase
{

    /**
     * @test
     */
    function it_transforms_arrays_as_is()
    {
        $transformer = new SimpleTransformer;
        $transformer->setEncoder($this->getMockEncoder());

        static::assertEquals(['data' => ['simple']], $transformer->transform(['simple']));
    }

    /**
     * @test
     */
    function it_transforms_non_arrays_by_casting_to_array()
    {
        $transformer = new SimpleTransformer;
        $transformer->setEncoder($this->getMockEncoder());

        static::assertEquals(['data' => ['simple']], $transformer->transform('simple'));
    }

    /**
     * @test
     */
    function it_transforms_arrayables_by_to_arraying()
    {
        $transformer = new SimpleTransformer;
        $transformer->setEncoder($this->getMockEncoder());

        $data  = new RelationData(['variable' => false, 'singular' => false]);
        $array = $data->toArray();

        static::assertEquals(['data' => $array], $transformer->transform($data));
    }


    /**
     * @return EncoderInterface|Mockery\MockInterface
     */
    protected function getMockEncoder()
    {
        return Mockery::mock(EncoderInterface::class);
    }

}
