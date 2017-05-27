<?php
namespace Czim\JsonApi\Test\Support\Resource;

use Czim\JsonApi\Support\Resource\ResourcePathHelper;
use Czim\JsonApi\Test\Helpers\Resources\AbstractTest\TestAbstractResource;
use Czim\JsonApi\Test\TestCase;

class ResourcePathHelperTest extends TestCase
{

    /**
     * @test
     */
    function it_returns_a_dasherized_relative_path_for_a_resource()
    {
        $this->app['config']->set('jsonapi.repository.resource.namespace', 'Czim\\JsonApi\\Test\\Helpers\\Resources\\');

        $resource = new TestAbstractResource;

        $helper = new ResourcePathHelper;

        static::assertEquals('abstract-test/test-resource', $helper->makePath($resource));
    }

    /**
     * @test
     */
    function it_uses_top_level_type_only_if_config_prefix_does_not_match()
    {
        $this->app['config']->set('jsonapi.repository.resource.namespace', 'Does\\NotMatch\\');

        $resource = new TestAbstractResource;

        $helper = new ResourcePathHelper;

        static::assertEquals(
            'test-resource',
            $helper->makePath($resource)
        );
    }

}
