<?php
namespace Czim\JsonApi\Test\Encoder\Transformers;

use Czim\JsonApi\Contracts\Encoder\EncoderInterface;
use Czim\JsonApi\Encoder\Transformers\ExceptionTransformer;
use Czim\JsonApi\Test\Helpers\Exceptions\TestStatusException;
use Czim\JsonApi\Test\TestCase;
use Illuminate\Http\Exceptions\HttpResponseException;
use Mockery;

/**
 * Class ExceptionTransformerTest
 *
 * @group encoding
 */
class ExceptionTransformerTest extends TestCase
{

    /**
     * @test
     */
    function it_transforms_an_exception_as_an_error()
    {
        $transformer = new ExceptionTransformer;
        $transformer->setEncoder($this->getMockEncoder());

        $exception = new \Exception('Some problem occurred');

        static::assertEquals(
            [
                'errors' => [
                    [
                        'title'  => 'Exception',
                        'status' => 500,
                        'detail' => 'Some problem occurred',
                    ],
                ],
            ],
            $transformer->transform($exception)
        );
    }

    /**
     * @test
     */
    function it_transforms_a_http_response_exception_as_an_error_using_its_status_code()
    {
        $transformer = new ExceptionTransformer;
        $transformer->setEncoder($this->getMockEncoder());

        $exception = new HttpResponseException(response()->json([], 418));

        static::assertEquals(
            [
                'errors' => [
                    [
                        'title'  => 'Http response exception',
                        'status' => 418,
                    ],
                ],
            ],
            $transformer->transform($exception)
        );
    }

    /**
     * @test
     */
    function it_transforms_an_exception_as_an_error_using_its_get_status_code_method()
    {
        $transformer = new ExceptionTransformer;
        $transformer->setEncoder($this->getMockEncoder());

        $exception = new TestStatusException('Some problem occurred');

        static::assertEquals(
            [
                'errors' => [
                    [
                        'title'  => 'Test status exception',
                        'status' => 418,
                        'detail' => 'Some problem occurred',
                    ],
                ],
            ],
            $transformer->transform($exception)
        );
    }

    /**
     * @test
     */
    function it_transforms_an_exception_as_an_error_using_a_mapped_status_code_for_the_exception_class()
    {
        $transformer = new ExceptionTransformer;
        $transformer->setEncoder($this->getMockEncoder());

        $this->app['config']->set('jsonapi.exceptions.status', [
            \Exception::class => 400,
        ]);

        $exception = new \Exception('Some problem occurred');

        static::assertEquals(
            [
                'errors' => [
                    [
                        'title'  => 'Exception',
                        'status' => 400,
                        'detail' => 'Some problem occurred',
                    ],
                ],
            ],
            $transformer->transform($exception)
        );
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    function it_throws_an_exception_if_data_is_not_an_exception()
    {
        $transformer = new ExceptionTransformer;
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
