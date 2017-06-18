<?php
namespace Czim\JsonApi\Test\Encoder\Transformers;

use Czim\JsonApi\Contracts\Encoder\EncoderInterface;
use Czim\JsonApi\Encoder\Transformers\ValidationExceptionTransformer;
use Czim\JsonApi\Exceptions\JsonApiValidationException;
use Czim\JsonApi\Test\TestCase;
use Mockery;

/**
 * Class ValidationExceptionTransformerTest
 *
 * @group encoding
 */
class ValidationExceptionTransformerTest extends TestCase
{

    /**
     * @test
     */
    function it_transforms_a_validation_exception_as_a_list_of_errors()
    {
        $transformer = new ValidationExceptionTransformer;
        $transformer->setEncoder($this->getMockEncoder());

        $exception = (new JsonApiValidationException('Validation problem'))
            ->setErrors(['test' => ['problem 1', 'problem 2'] ]);

        static::assertEquals(
            [
                'errors' => [
                    [
                        'title'  => 'Validation problem',
                        'status' => 422,
                        'detail' => 'problem 1',
                        'source' => ['pointer' => 'test'],
                    ],
                    [
                        'title'  => 'Validation problem',
                        'status' => 422,
                        'detail' => 'problem 2',
                        'source' => ['pointer' => 'test'],
                    ],
                ],
            ],
            $transformer->transform($exception)
        );
    }

    /**
     * @test
     */
    function it_transforms_a_validation_exception_as_a_merged_error_object_per_key_if_configured_to()
    {
        $this->app['config']->set('jsonapi.transform.group-validation-errors-by-key', true);

        $transformer = new ValidationExceptionTransformer;
        $transformer->setEncoder($this->getMockEncoder());

        $exception = (new JsonApiValidationException('Validation problem'))
            ->setErrors(['test' => ['problem 1', 'problem 2'], 'separate' => ['problem 3'] ]);

        static::assertEquals(
            [
                'errors' => [
                    [
                        'title'  => 'Validation problem',
                        'status' => 422,
                        'detail' => "problem 1\nproblem 2",
                        'source' => ['pointer' => 'test'],
                    ],
                    [
                        'title'  => 'Validation problem',
                        'status' => 422,
                        'detail' => 'problem 3',
                        'source' => ['pointer' => 'separate'],
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
    function it_throws_an_exception_if_data_is_not_a_jsonapi_validation_exception()
    {
        $transformer = new ValidationExceptionTransformer;
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
