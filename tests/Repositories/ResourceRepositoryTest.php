<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\JsonApi\Test\Repositories;

use Czim\JsonApi\Contracts\Repositories\ResourceCollectorInterface;
use Czim\JsonApi\Contracts\Resource\ResourceInterface;
use Czim\JsonApi\Repositories\ResourceRepository;
use Czim\JsonApi\Test\Helpers\Models\TestAlternativeModel;
use Czim\JsonApi\Test\Helpers\Models\TestSimpleModel;
use Czim\JsonApi\Test\Helpers\Resources\TestAlternativeModelResource;
use Czim\JsonApi\Test\Helpers\Resources\TestSimpleModelResource;
use Czim\JsonApi\Test\TestCase;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Mockery;

class ResourceRepositoryTest extends TestCase
{

    /**
     * @test
     */
    function it_returns_manually_registered_resource()
    {
        $collector = $this->getMockCollector();
        $collector->shouldReceive('collect')->once()->andReturn(new Collection);

        $repository = new ResourceRepository($collector);

        $repository->register(TestSimpleModel::class, TestSimpleModelResource::class);

        $all = $repository->getAll();

        static::assertInstanceOf(Collection::class, $all);
        static::assertCount(1, $all);
        static::assertInstanceOf(ResourceInterface::class, $all->first());
    }

    /**
     * @test
     */
    function it_returns_resource_by_model()
    {
        $collector = $this->getMockCollector();
        $collector->shouldReceive('collect')->andReturn(new Collection);

        $repository = new ResourceRepository($collector);

        $repository->register(TestSimpleModel::class, TestSimpleModelResource::class);

        static::assertInstanceOf(TestSimpleModelResource::class, $repository->getByModel(new TestSimpleModel));
        static::assertInstanceOf(TestSimpleModelResource::class, $repository->getByModelClass(TestSimpleModel::class));
    }

    /**
     * @test
     */
    function it_registers_a_model_by_instance()
    {
        $collector = $this->getMockCollector();
        $collector->shouldReceive('collect')->once()->andReturn(new Collection);

        $repository = new ResourceRepository($collector);

        $repository->register(new TestSimpleModel, TestSimpleModelResource::class);
        static::assertInstanceOf(TestSimpleModelResource::class, $repository->getByModelClass(TestSimpleModel::class));
    }

    /**
     * @test
     */
    function it_combines_collected_and_manually_registered_resources()
    {
        $collector = $this->getMockCollector();
        $collector->shouldReceive('collect')->andReturn(new Collection([
            'test-alternative-models' => (new TestAlternativeModelResource)->setModel(new TestAlternativeModel),
        ]));

        $repository = new ResourceRepository($collector);

        $repository->register(TestSimpleModel::class, TestSimpleModelResource::class);

        static::assertInstanceOf(
            TestSimpleModelResource::class,
            $repository->getByModelClass(TestSimpleModel::class)
        );
        static::assertInstanceOf(
            TestAlternativeModelResource::class,
            $repository->getByModelClass(TestAlternativeModel::class)
        );
    }

    /**
     * @test
     */
    function it_returns_resource_by_type()
    {
        $collector = $this->getMockCollector();
        $collector->shouldReceive('collect')->andReturn(new Collection);

        $repository = new ResourceRepository($collector);

        $repository->register(TestSimpleModel::class, TestSimpleModelResource::class);

        static::assertInstanceOf(TestSimpleModelResource::class, $repository->getByType('test-simple-models'));
    }

    /**
     * @test
     */
    function it_returns_null_when_resource_could_not_be_found()
    {
        $collector = $this->getMockCollector();
        $collector->shouldReceive('collect')->andReturn(new Collection);

        $repository = new ResourceRepository($collector);

        static::assertNull($repository->getByModel(new TestSimpleModel));
        static::assertNull($repository->getByModelClass(TestSimpleModel::class));
        static::assertNull($repository->getByType('unknown-type'));
    }

    /**
     * @test
     */
    function it_only_initializes_once()
    {
        $collector = $this->getMockCollector();
        $collector->shouldReceive('collect')->once()->andReturn(new Collection);

        $repository = new ResourceRepository($collector);

        $repository->getAll();
        $repository->getAll();
    }

    /**
     * @test
     */
    function it_throws_an_exception_if_a_registered_resource_is_invalid()
    {
        $this->expectException(InvalidArgumentException::class);

        $collector = $this->getMockCollector();
        $collector->shouldReceive('collect')->andReturn(new Collection);

        $repository = new ResourceRepository($collector);

        $repository->register(TestSimpleModel::class, TestSimpleModel::class);
        $repository->getByModelClass(TestSimpleModel::class);
    }

    /**
     * @return ResourceCollectorInterface|Mockery\MockInterface
     */
    protected function getMockCollector()
    {
        return Mockery::mock(ResourceCollectorInterface::class);
    }

}
