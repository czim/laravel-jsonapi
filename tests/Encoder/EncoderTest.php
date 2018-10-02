<?php
namespace Czim\JsonApi\Test\Encoder\Factories;

use Czim\JsonApi\Contracts\Encoder\TransformerFactoryInterface;
use Czim\JsonApi\Contracts\Encoder\TransformerInterface;
use Czim\JsonApi\Contracts\Repositories\ResourceRepositoryInterface;
use Czim\JsonApi\Contracts\Resource\ResourceInterface;
use Czim\JsonApi\Encoder\Encoder;
use Czim\JsonApi\Test\Helpers\Models\TestSimpleModel;
use Czim\JsonApi\Test\TestCase;
use Mockery;

/**
 * Class EncoderTest
 *
 * @group encoding
 */
class EncoderTest extends TestCase
{

    /**
     * @test
     */
    function it_encodes_simple_data()
    {
        $factoryMock     = $this->getMockFactory();
        $repositoryMock  = $this->getMockResourceRepository();
        $transformerMock = $this->getMockTransformer();

        $transformerMock->shouldReceive('setEncoder')->andReturnSelf();
        $transformerMock->shouldReceive('setIsTop')->andReturnSelf();
        $transformerMock->shouldReceive('transform')->with('simple')->andReturn(['data' => 'simple']);
        $factoryMock->shouldReceive('makeFor')->with('simple')->once()->andReturn($transformerMock);

        $encoder = new Encoder($factoryMock, $repositoryMock);

        static::assertEquals(['data' => 'simple'], $encoder->encode('simple'));
    }

    /**
     * @test
     */
    function it_encodes_simple_data_with_requested_includes()
    {
        $factoryMock     = $this->getMockFactory();
        $repositoryMock  = $this->getMockResourceRepository();
        $transformerMock = $this->getMockTransformer();

        $transformerMock->shouldReceive('setEncoder')->andReturnSelf();
        $transformerMock->shouldReceive('setIsTop')->andReturnSelf();
        $transformerMock->shouldReceive('transform')->andReturn(['data' => 'simple']);
        $factoryMock->shouldReceive('makeFor')->with('simple')->once()->andReturn($transformerMock);

        $encoder = new Encoder($factoryMock, $repositoryMock);

        $encoder->encode('simple', ['some.include']);

        static::assertTrue($encoder->isIncludeRequested('some.include'));
    }

    /**
     * @test
     * @depends it_encodes_simple_data
     */
    function it_sets_and_removes_top_level_links_and_uses_them_when_encoding()
    {
        $factoryMock     = $this->getMockFactory();
        $repositoryMock  = $this->getMockResourceRepository();
        $transformerMock = $this->getMockTransformer();

        $transformerMock->shouldReceive('setEncoder')->andReturnUsing(
            function (Encoder $encoder) use ($transformerMock) {
                // Set top level link to affect output
                $encoder
                    ->setLink('self', 'some/link/here')
                    ->setLink('related', 'another/link/here')
                    ->removeLink('related');
                return $transformerMock;
            }
        );
        $transformerMock->shouldReceive('setIsTop')->andReturnSelf();
        $transformerMock->shouldReceive('transform')->with('simple')->andReturn(['data' => 'simple']);
        $factoryMock->shouldReceive('makeFor')->with('simple')->once()->andReturn($transformerMock);

        $encoder = new Encoder($factoryMock, $repositoryMock);

        static::assertEquals(
            [
                'data'  => 'simple',
                'links' => ['self' => 'some/link/here'],
            ],
            $encoder->encode('simple')
        );
    }

    /**
     * @test
     * @depends it_encodes_simple_data
     */
    function it_sets_and_removes_sideloaded_includes_and_uses_them_when_encoding()
    {
        $factoryMock     = $this->getMockFactory();
        $repositoryMock  = $this->getMockResourceRepository();
        $transformerMock = $this->getMockTransformer();

        $transformerMock->shouldReceive('setEncoder')->andReturnUsing(
            function (Encoder $encoder) use ($transformerMock) {
                // Set included data to affect output
                $encoder
                    ->addIncludedData(['testing'], 'some-type:13')
                    ->addIncludedData(['something'], 'some-type:16')
                    ->addIncludedData(['again'])
                    ->removeFromIncludedDataByTypeAndId('some-type', '16');
                return $transformerMock;
            }
        );
        $transformerMock->shouldReceive('setIsTop')->andReturnSelf();
        $transformerMock->shouldReceive('transform')->with('simple')->andReturn(['data' => 'simple']);
        $factoryMock->shouldReceive('makeFor')->with('simple')->once()->andReturn($transformerMock);

        $encoder = new Encoder($factoryMock, $repositoryMock);

        static::assertEquals(
            [
                'data'     => 'simple',
                'included' => [ ['testing'], ['again'] ],
            ],
            $encoder->encode('simple')
        );
    }

    /**
     * @test
     */
    function it_removes_top_level_resource_from_included_data()
    {
        $factoryMock     = $this->getMockFactory();
        $repositoryMock  = $this->getMockResourceRepository();
        $transformerMock = $this->getMockTransformer();

        $transformerMock->shouldReceive('setEncoder')->andReturnUsing(
            function (Encoder $encoder) use ($transformerMock) {
                // Set included data to affect output
                $encoder
                    ->addIncludedData(['type' => 'some-type', 'id' => '13'], 'some-type:13')
                    ->addIncludedData(['type' => 'some-type', 'id' => '16'], 'some-type:16');
                return $transformerMock;
            }
        );
        $transformerMock->shouldReceive('setIsTop')->andReturnSelf();
        $transformerMock->shouldReceive('transform')->with('simple')
            ->andReturn(['data' => ['type' => 'some-type', 'id' => '13'] ]);
        $factoryMock->shouldReceive('makeFor')->with('simple')->once()->andReturn($transformerMock);

        $encoder = new Encoder($factoryMock, $repositoryMock);

        static::assertEquals(
            [
                'data'     => ['type' => 'some-type', 'id' => '13'],
                'included' => [ ['type' => 'some-type', 'id' => '16'] ],
            ],
            $encoder->encode('simple')
        );
    }


    // ------------------------------------------------------------------------------
    //      Data
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_sets_and_retrieves_requested_includes()
    {
        $encoder = new Encoder($this->getMockFactory(), $this->getMockResourceRepository());

        static::assertFalse($encoder->hasRequestedIncludes());

        static::assertSame($encoder, $encoder->setRequestedIncludes(['first', 'second']));

        static::assertTrue($encoder->hasRequestedIncludes());

        static::assertEquals(['first', 'second'], $encoder->getRequestedIncludes());
    }
    
    /**
     * @test
     * @depends it_sets_and_retrieves_requested_includes
     */
    function it_reports_whether_a_given_include_is_requested()
    {
        $encoder = new Encoder($this->getMockFactory(), $this->getMockResourceRepository());

        $encoder->setRequestedIncludes(['first', 'second.another']);

        static::assertTrue($encoder->isIncludeRequested('first'));
        static::assertTrue($encoder->isIncludeRequested('second'));
        static::assertTrue($encoder->isIncludeRequested('second.another'));

        static::assertFalse($encoder->isIncludeRequested('not-included'));
        static::assertFalse($encoder->isIncludeRequested('second.notset'));
    }
    
    /**
     * @test
     */
    function it_returns_base_url()
    {
        $encoder = new Encoder($this->getMockFactory(), $this->getMockResourceRepository());

        $this->app['config']->set('jsonapi.base_url', '/testing');

        static::assertEquals('/testing', $encoder->getBaseUrl());
    }

    /**
     * @test
     */
    function it_sets_and_returns_a_top_resource_url()
    {
        $this->app['config']->set('jsonapi.base_url', '/testing');

        $encoder = new Encoder($this->getMockFactory(), $this->getMockResourceRepository());

        static::assertNull($encoder->getTopResourceUrl());

        // Relative URL
        static::assertSame($encoder, $encoder->setTopResourceUrl('test-top-resource'));
        static::assertEquals('/testing/test-top-resource', $encoder->getTopResourceUrl());

        // Absolute URL
        static::assertSame($encoder, $encoder->setTopResourceUrl('absolute/test-top-resource', true));
        static::assertEquals('absolute/test-top-resource', $encoder->getTopResourceUrl());
    }

    /**
     * @test
     */
    function it_sets_adds_removes_and_returns_top_level_meta_data()
    {
        $encoder = new Encoder($this->getMockFactory(), $this->getMockResourceRepository());

        static::assertEquals([], $encoder->getMeta());

        static::assertSame($encoder, $encoder->removeMetaKey('does-not-exist'));

        static::assertSame($encoder, $encoder->addMeta('a', 'first'));
        static::assertEquals(['a' => 'first'], $encoder->getMeta());

        static::assertSame($encoder, $encoder->setMeta(['b' => 'test']));
        static::assertEquals(['b' => 'test'], $encoder->getMeta());

        static::assertSame($encoder, $encoder->addMeta('c', 'another'));
        static::assertEquals(['b' => 'test', 'c' => 'another'], $encoder->getMeta());

        static::assertSame($encoder, $encoder->removeMetaKey('b'));
        static::assertEquals(['c' => 'another'], $encoder->getMeta());
    }

    /**
     * @test
     */
    function it_uses_the_resource_repository_to_return_resource_by_model()
    {
        $resourceMock = $this->getMockResource();
        $resourceMock->shouldReceive('setModel')->with(Mockery::type(TestSimpleModel::class))->once();

        $repositoryMock = $this->getMockResourceRepository();
        $repositoryMock->shouldReceive('getByModel')->with(Mockery::type(TestSimpleModel::class))
            ->andReturn(null, $resourceMock);

        $encoder = new Encoder($this->getMockFactory(), $repositoryMock);

        static::assertNull($encoder->getResourceForModel(new TestSimpleModel), 'first call fails');
        static::assertSame($resourceMock, $encoder->getResourceForModel(new TestSimpleModel), 'second call fails');
    }

    /**
     * @test
     */
    function it_uses_the_resource_repository_to_return_resource_by_type()
    {
        $resourceMock = $this->getMockResource();

        $repositoryMock = $this->getMockResourceRepository();
        $repositoryMock->shouldReceive('getByType')->with('some-type')->andReturn($resourceMock);

        $encoder = new Encoder($this->getMockFactory(), $repositoryMock);

        static::assertSame($resourceMock, $encoder->getResourceForType('some-type'));
    }


    /**
     * @return TransformerFactoryInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockFactory()
    {
        return Mockery::mock(TransformerFactoryInterface::class);
    }

    /**
     * @return ResourceRepositoryInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockResourceRepository()
    {
        return Mockery::mock(ResourceRepositoryInterface::class);
    }

    /**
     * @return TransformerInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockTransformer()
    {
        return Mockery::mock(TransformerInterface::class);
    }

    /**
     * @return ResourceInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockResource()
    {
        return Mockery::mock(ResourceInterface::class);
    }

}
