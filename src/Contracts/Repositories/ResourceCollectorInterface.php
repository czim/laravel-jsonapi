<?php
namespace Czim\JsonApi\Contracts\Repositories;

use Illuminate\Support\Collection;
use Czim\JsonApi\Contracts\Resource\ResourceInterface;

interface ResourceCollectorInterface
{

    /**
     * Collects all relevant resources.
     *
     * @return Collection|ResourceInterface[]
     */
    public function collect();

}
