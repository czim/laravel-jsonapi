<?php
namespace Czim\JsonApi\Test\Http\Middleware;

use Czim\JsonApi\Http\Middleware\RequireJsonApiHeader;
use Czim\JsonApi\Test\TestCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mockery;

/**
 * Class RequireJsonApiHeaderTest
 *
 * @group http
 */
class RequireJsonApiHeaderTest extends TestCase
{

    /**
     * @test
     */
    function it_passes_through_if_accept_and_content_type_headers_are_valid()
    {
        /** @var Request|Mockery\Mock $requestMock */
        $requestMock = Mockery::mock(Request::class);
        $requestMock->shouldReceive('header')->with('accept')->once()->andReturn('application/vnd.api+json');
        $requestMock->shouldReceive('header')->with('content-type')->once()->andReturn('application/vnd.api+json');

        $middleware = new RequireJsonApiHeader;

        $next = function ($request) { return $request; };

        static::assertSame($requestMock, $middleware->handle($requestMock, $next));
    }

    /**
     * @test
     */
    function it_returns_406_status_code_if_accept_header_is_invalid()
    {
        /** @var Request|Mockery\Mock $requestMock */
        $requestMock = Mockery::mock(Request::class);
        $requestMock->shouldReceive('header')->with('accept')->once()->andReturn('application/json');

        $middleware = new RequireJsonApiHeader;

        $next = function ($request) { return $request; };

        $response = $middleware->handle($requestMock, $next);

        static::assertInstanceOf(Response::class, $response);
        static::assertEquals(406, $response->getStatusCode());
    }

    /**
     * @test
     */
    function it_returns_406_status_code_if_content_type_header_is_invalid()
    {
        /** @var Request|Mockery\Mock $requestMock */
        $requestMock = Mockery::mock(Request::class);
        $requestMock->shouldReceive('header')->with('accept')->once()->andReturn('application/vnd.api+json');
        $requestMock->shouldReceive('header')->with('content-type')->once()->andReturn('text/html');

        $middleware = new RequireJsonApiHeader;

        $next = function ($request) { return $request; };

        $response = $middleware->handle($requestMock, $next);

        static::assertInstanceOf(Response::class, $response);
        static::assertEquals(415, $response->getStatusCode());
    }

    /**
     * @test
     * @depends it_passes_through_if_accept_and_content_type_headers_are_valid
     */
    function it_accepts_application_json_for_content_type()
    {
        /** @var Request|Mockery\Mock $requestMock */
        $requestMock = Mockery::mock(Request::class);
        $requestMock->shouldReceive('header')->with('accept')->once()->andReturn('application/vnd.api+json');
        $requestMock->shouldReceive('header')->with('content-type')->once()->andReturn('application/json');

        $middleware = new RequireJsonApiHeader;

        $next = function ($request) { return $request; };

        static::assertSame($requestMock, $middleware->handle($requestMock, $next));
    }

    /**
     * @test
     * @depends it_passes_through_if_accept_and_content_type_headers_are_valid
     */
    function it_accepts_multipart_formdata_for_content_type()
    {
        /** @var Request|Mockery\Mock $requestMock */
        $requestMock = Mockery::mock(Request::class);
        $requestMock->shouldReceive('header')->with('accept')->once()->andReturn('application/vnd.api+json');
        $requestMock->shouldReceive('header')->with('content-type')->once()->andReturn('multipart/form-data');

        $middleware = new RequireJsonApiHeader;

        $next = function ($request) { return $request; };

        static::assertSame($requestMock, $middleware->handle($requestMock, $next));
    }

    /**
     * @test
     */
    function it_accepts_a_content_type_attribute_only_if_it_is_charset_utf8()
    {
        /** @var Request|Mockery\Mock $requestMock */
        $requestMock = Mockery::mock(Request::class);
        $requestMock->shouldReceive('header')->with('accept')->once()->andReturn('application/vnd.api+json');
        $requestMock->shouldReceive('header')->with('content-type')->once()
            ->andReturn('application/vnd.api+json; charset=utf-8');

        $middleware = new RequireJsonApiHeader;

        $next = function ($request) { return $request; };

        static::assertSame($requestMock, $middleware->handle($requestMock, $next));
    }

}
