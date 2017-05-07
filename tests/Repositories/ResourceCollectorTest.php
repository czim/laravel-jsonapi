<?php
namespace Czim\JsonApi\Test\Repositories;

use Czim\JsonApi\Repositories\ResourceCollector;
use Czim\JsonApi\Test\Helpers\Models\TestComment;
use Czim\JsonApi\Test\Helpers\Models\TestPost;
use Czim\JsonApi\Test\Helpers\Resources\TestCommentResource;
use Czim\JsonApi\Test\Helpers\Resources\TestPostResource;
use Czim\JsonApi\Test\TestCase;
use Illuminate\Support\Collection;

class ResourceCollectorTest extends TestCase
{

    /**
     * @test
     */
    function collects_resources_mapped_by_config()
    {
        $this->app['config']->set('jsonapi.repository.resource.collect', false);
        $this->app['config']->set('jsonapi.repository.resource.map', [
            TestPost::class    => TestPostResource::class,
            TestComment::class => TestCommentResource::class,
        ]);

        $collector = new ResourceCollector();
        $collected = $collector->collect();

        static::assertInstanceOf(Collection::class, $collected);
        static::assertCount(2, $collected);
        static::assertEquals(['test-posts', 'test-comments'], $collected->keys()->toArray());
        static::assertInstanceof(TestPostResource::class, $collected->get('test-posts'));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    function it_throws_an_exception_if_a_mapped_resource_class_does_not_implement_the_correct_interface()
    {
        $this->app['config']->set('jsonapi.repository.resource.collect', false);
        $this->app['config']->set('jsonapi.repository.resource.map', [
            TestPost::class => static::class,
        ]);

        $collector = new ResourceCollector();
        $collector->collect();
    }

}
