<?php
namespace Czim\JsonApi\Contracts\Repositories;

use Czim\JsonApi\Contracts\Resource\ResourceInterface;
use Illuminate\Support\Collection;

interface ResourceCollectorInterface
{

    /**
     * Collects all relevant resources.
     *
     * @return Collection|ResourceInterface[]
     */
    public function collect();

}
