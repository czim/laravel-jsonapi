<?php
namespace Czim\JsonApi\Repositories;

use Czim\JsonApi\Contracts\Repositories\ResourceCollectorInterface;
use Czim\JsonApi\Contracts\Resource\EloquentResourceInterface;
use Czim\JsonApi\Contracts\Resource\ResourceInterface;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class ResourceCollector implements ResourceCollectorInterface
{
    /**
     * Collects all relevant resources.
     *
     * These must have a model set (may be unpersisted new model instance).
     *
     * @return Collection|ResourceInterface[]   keyed by model class string
     */
    public function collect(): Collection
    {
        $resources = new Collection;


        if (config('jsonapi.repository.resource.collect')) {
            $mapped = $this->collectByNamespace();

            if ( ! $mapped->isEmpty()) {
                $resources = $resources->merge($mapped);
            }
        }


        $mapped = $this->collectByMapping();

        if ( ! $mapped->isEmpty()) {
            $resources = $resources->merge($mapped);
        }

        return $resources;
    }

    /**
     * Collects resources mapped by configuration.
     *
     * @return Collection|ResourceInterface[]   keyed by model class string
     */
    protected function collectByMapping(): Collection
    {
        $mapping = config('jsonapi.repository.resource.map', []);
        $mapped  = new Collection;

        foreach ($mapping as $modelClass => $resourceClass) {

            $resource = $this->instantiateResource($resourceClass);
            $resource->setModel(new $modelClass);

            $mapped->put($resource->type(), $resource);
        }

        return $mapped;
    }

    /**
     * Collects resources mapped by name/namespace.
     *
     * @return Collection|ResourceInterface[]   keyed by model class string
     */
    protected function collectByNamespace(): Collection
    {
        // todo
        // launch resource-reader
        // which should traverse the namespace to find all resources
        // and later might have caching

        return new Collection;
    }

    /**
     * Makes an instance of a resource by FQN.
     *
     * @param string $class
     * @return ResourceInterface|EloquentResourceInterface
     */
    protected function instantiateResource(string $class): ResourceInterface
    {
        $resource = app($class);

        if ( ! ($resource instanceof ResourceInterface)) {
            throw new InvalidArgumentException("{$class} does not implement ResourceInterface");
        }

        return $resource;
    }
}
