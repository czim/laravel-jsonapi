<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\JsonApi\Test\Support\Error;

use Czim\JsonApi\Support\Error\ErrorData;
use Czim\JsonApi\Test\TestCase;

class ErrorDataTest extends TestCase
{

    /**
     * @test
     */
    function it_returns_id()
    {
        $data = new ErrorData;

        static::assertSame('', $data->id());

        $data->id = 'test';

        static::assertSame('test', $data->id());
    }

    /**
     * @test
     */
    function it_returns_links()
    {
        $data = new ErrorData;

        static::assertEquals([], $data->links());

        $data->links = ['test'];

        static::assertEquals(['test'], $data->links());
    }

    /**
     * @test
     */
    function it_returns_status()
    {
        $data = new ErrorData;

        static::assertSame('', $data->status());

        $data->status = 'test';

        static::assertSame('test', $data->status());
    }

    /**
     * @test
     */
    function it_returns_code()
    {
        $data = new ErrorData;

        static::assertSame('', $data->code());

        $data->code = 'test';

        static::assertSame('test', $data->code());
    }

    /**
     * @test
     */
    function it_returns_title()
    {
        $data = new ErrorData;

        static::assertSame('', $data->title());

        $data->title = 'test';

        static::assertSame('test', $data->title());
    }

    /**
     * @test
     */
    function it_returns_detail()
    {
        $data = new ErrorData;

        static::assertSame('', $data->detail());

        $data->detail = 'test';

        static::assertSame('test', $data->detail());
    }

    /**
     * @test
     */
    function it_returns_source()
    {
        $data = new ErrorData;

        static::assertEquals([], $data->source());

        $data->source = ['test'];

        static::assertEquals(['test'], $data->source());
    }

    /**
     * @test
     */
    function it_returns_meta()
    {
        $data = new ErrorData;

        static::assertEquals([], $data->meta());

        $data->meta = ['test'];

        static::assertEquals(['test'], $data->meta());
    }

    /**
     * @test
     */
    function it_returns_a_clean_to_array()
    {
        $data = new ErrorData;

        $data->links = ['testing'];
        $data->meta  = [];

        static::assertCount(1, $data->toCleanArray());
    }

}
