<?php
namespace Czim\JsonApi\Test\Support\Request;

use Czim\JsonApi\Support\Request\RequestQueryParser;
use Czim\JsonApi\Test\TestCase;
use Illuminate\Http\Request;
use Mockery;

class RequestParserTest extends TestCase
{

    protected $filter  = [];
    protected $include = null;
    protected $page    = [];
    protected $sort    = null;

    /**
     * @test
     */
    function it_returns_filter_data()
    {
        $this->filter = ['id' => 2];

        $parser = new RequestQueryParser($this->getSetUpRequest());

        static::assertEquals(['id' => 2], $parser->getFilter());
    }

    /**
     * @test
     */
    function it_returns_filter_value()
    {
        $this->filter = ['id' => 2];

        $parser = new RequestQueryParser($this->getSetUpRequest());

        static::assertEquals(2, $parser->getFilterValue('id'));
    }

    /**
     * @test
     */
    function it_returns_default_value_if_filter_value_not_set()
    {
        $this->page = ['id' => 2];

        $parser = new RequestQueryParser($this->getSetUpRequest());

        static::assertEquals(333, $parser->getFilterValue('test', 333));
    }

    /**
     * @test
     */
    function it_returns_raw_includes()
    {
        $this->include = 'test,include.attribute';

        $parser = new RequestQueryParser($this->getSetUpRequest());

        static::assertEquals('test,include.attribute', $parser->getRawIncludes());
    }

    /**
     * @test
     */
    function it_returns_include_array()
    {
        $this->include = 'test,include.attribute';

        $parser = new RequestQueryParser($this->getSetUpRequest());

        static::assertEquals(['test', 'include.attribute'], $parser->getIncludes());
    }

    /**
     * @test
     */
    function it_returns_page_data()
    {
        $this->page = ['number' => 2];

        $parser = new RequestQueryParser($this->getSetUpRequest());

        static::assertEquals(['number' => 2], $parser->getPageData());
    }

    /**
     * @test
     */
    function it_returns_page_number()
    {
        $this->page = ['number' => 2];

        $parser = new RequestQueryParser($this->getSetUpRequest());

        static::assertEquals(2, $parser->getPageNumber());
    }

    /**
     * @test
     */
    function it_returns_page_size()
    {
        $this->page = ['size' => 10];

        $parser = new RequestQueryParser($this->getSetUpRequest());

        static::assertEquals(10, $parser->getPageSize());
    }

    /**
     * @test
     */
    function it_returns_page_offset()
    {
        $this->page = ['offset' => 10];

        $parser = new RequestQueryParser($this->getSetUpRequest());

        static::assertEquals(10, $parser->getPageOffset());
    }

    /**
     * @test
     */
    function it_returns_page_limit()
    {
        $this->page = ['limit' => 20];

        $parser = new RequestQueryParser($this->getSetUpRequest());

        static::assertEquals(20, $parser->getPageLimit());
    }

    /**
     * @test
     */
    function it_returns_page_cursor()
    {
        $this->page = ['cursor' => 15];

        $parser = new RequestQueryParser($this->getSetUpRequest());

        static::assertEquals(15, $parser->getPageCursor());
    }

    /**
     * @test
     */
    function it_returns_raw_sorting_string()
    {
        $this->sort = 'test,include|desc';

        $parser = new RequestQueryParser($this->getSetUpRequest());

        static::assertEquals('test,include|desc', $parser->getRawSort());
    }

    /**
     * @test
     */
    function it_returns_sort_array()
    {
        $this->sort = 'test,include|desc';

        $parser = new RequestQueryParser($this->getSetUpRequest());

        static::assertEquals(['test', 'include|desc'], $parser->getSort());
    }


    /**
     * @return Request|Mockery\Mock
     */
    protected function getSetUpRequest()
    {
        /** @var Request|Mockery\Mock $request */
        $request = Mockery::mock(Request::class);

        $request->shouldReceive('query')->with('filter', Mockery::any())->andReturn($this->filter);
        $request->shouldReceive('query')->with('include')->andReturn($this->include);
        $request->shouldReceive('query')->with('page', Mockery::any())->andReturn($this->page);
        $request->shouldReceive('query')->with('sort')->andReturn($this->sort);

        return $request;
    }

}
