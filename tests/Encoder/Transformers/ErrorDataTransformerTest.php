<?php
namespace Czim\JsonApi\Test\Encoder\Transformers;

use Czim\JsonApi\Contracts\Encoder\EncoderInterface;
use Czim\JsonApi\Encoder\Transformers\ErrorDataTransformer;
use Czim\JsonApi\Support\Error\ErrorData;
use Czim\JsonApi\Test\TestCase;
use Mockery;

/**
 * Class ErrorDataTransformerTest
 *
 * @group encoding
 */
class ErrorDataTransformerTest extends TestCase
{

    /**
     * @test
     * @uses \Czim\JsonApi\Support\Error\ErrorData
     */
    function it_transforms_an_error_data_object_as_a_clean_array()
    {
        $transformer = new ErrorDataTransformer;
        $transformer->setEncoder($this->getMockEncoder());

        $data = new ErrorData([
            'title' => 'Testing',
        ]);

        static::assertEquals(
            [
                'errors' => [
                    ['title' => 'Testing'],
                ],
            ],
            $transformer->transform($data)
        );
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    function it_throws_an_exception_if_data_is_not_an_error_object()
    {
        $transformer = new ErrorDataTransformer;
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
