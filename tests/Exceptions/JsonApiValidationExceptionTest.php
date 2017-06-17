<?php
namespace Czim\JsonApi\Test\Exceptions;

use Czim\JsonApi\Exceptions\JsonApiValidationException;
use Czim\JsonApi\Test\TestCase;

class JsonApiValidationExceptionTest extends TestCase
{

    /**
     * @test
     */
    function it_sets_and_returns_the_prefix()
    {
        $exception = new JsonApiValidationException;

        $exception->setPrefix('testing-prefix/');

        static::assertEquals('testing-prefix/', $exception->getPrefix());
    }

}
