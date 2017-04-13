<?php
namespace Czim\JsonApi\Repositories;

use Czim\JsonApi\Contracts\Repositories\ResourceCollectorInterface;
use Czim\JsonApi\Contracts\Resource\ResourceInterface;
use Illuminate\Support\Collection;

class ResourceCollector implements ResourceCollectorInterface
{

    /**
     * Collects all relevant resources.
     *
     * These must have a model set (may be unpersisted new model instance).
     *
     * @return Collection|ResourceInterface[]
     */
    public function collect()
    {
        // todo
        // launch resource-reader
        // which should traverse the namespace to find all resources
        // and later might have caching

        return new Collection;
    }

}
