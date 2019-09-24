<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\JsonApi\Test\Support;

use Czim\JsonApi\Contracts\Encoder\EncoderInterface;
use Czim\JsonApi\Http\Requests\JsonApiCreateRequest;
use Czim\JsonApi\Http\Requests\JsonApiRequest;
use Czim\JsonApi\Http\Responses\JsonApiResponse;
use Czim\JsonApi\Support\Request\RequestQueryParser;
use Czim\JsonApi\Test\TestCase;
use Illuminate\Http\Request;
use Mockery;

class HelpersTest extends TestCase
{

    /**
     * @test
     */
    function helper_method_returns_a_jsonapi_request()
    {
        $this->app->instance(JsonApiRequest::class, 'testing');

        static::assertEquals('testing', jsonapi_request());
    }

    /**
     * @test
     */
    function helper_method_returns_a_jsonapi_create_request()
    {
        $this->app->instance(JsonApiCreateRequest::class, 'testing');

        static::assertEquals('testing', jsonapi_request_create());
    }

    /**
     * @test
     */
    function helper_method_returns_a_jsonapi_query_parser()
    {
        $this->app->instance(RequestQueryParser::class, 'testing');

        static::assertEquals('testing', jsonapi_query());
    }

    /**
     * @test
     */
    function helper_method_returns_a_jsonapi_response()
    {
        $response = jsonapi_response(['test'], 422);

        static::assertInstanceOf(JsonApiResponse::class, $response);
        static::assertEquals(422, $response->getStatusCode());
        static::assertEquals('application/vnd.api+json', $response->headers->get('content-type'));
    }

    /**
     * @test
     */
    function helper_method_encodes_data()
    {
        /** @var EncoderInterface|Mockery\Mock $encoderMock */
        $encoderMock = Mockery::mock(EncoderInterface::class);
        $encoderMock->shouldReceive('encode')->with('data', ['include'])->once()->andReturn('encoder output');

        $this->app->instance(EncoderInterface::class, $encoderMock);

        static::assertEquals('encoder output', jsonapi_encode('data', ['include']));
    }

    /**
     * @test
     */
    function helper_method_encodes_error_response()
    {
        /** @var EncoderInterface|Mockery\Mock $encoderMock */
        $encoderMock = Mockery::mock(EncoderInterface::class);
        $encoderMock->shouldReceive('encode')->with('problem', Mockery::any())->once()->andReturn('encoder output');

        $this->app->instance(EncoderInterface::class, $encoderMock);

        $response = jsonapi_error('problem');

        static::assertInstanceOf(JsonApiResponse::class, $response);
        static::assertEquals('encoder output', $response->getData());
    }

    /**
     * @test
     */
    function helper_method_returns_whether_current_request_is_jsonapi()
    {
        /** @var Request|Mockery\Mock $requestMock */
        $requestMock = Mockery::mock(Request::class . '[header]');
        $requestMock->shouldReceive('header')->with('accept')->twice()->andReturn('application/vnd.api+json', 'text/plain');

        $this->app->instance('request', $requestMock);

        static::assertTrue(is_jsonapi_request(), 'First check should be true');
        static::assertFalse(is_jsonapi_request(), 'Second check should be false');
    }

}
