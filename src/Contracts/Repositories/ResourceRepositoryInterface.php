<?php
namespace Czim\JsonApi\Contracts\Repositories;

use Czim\JsonApi\Contracts\Resource\ResourceInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface ResourceRepositoryInterface
{

    /**
     * Initializes repository, registering resources where possible.
     */
    public function initialize();

    /**
     * Registers a resource instance for a given model or model class.
     *
     * @param Model|string             $model       class or instance
     * @param ResourceInterface|string $resource    class or instance
     * @return $this
     */
    public function register($model, $resource);

    /**
     * Returns all registered resources.
     *
     * @return Collection|ResourceInterface[]
     */
    public function getAll();

    /**
     * Returns resource for JSON-API type, if available.
     *
     * @param string $type
     * @return ResourceInterface|null
     */
    public function getByType($type);

    /**
     * Returns resource for given model instance, if available.
     *
     * @param Model $model
     * @return null|ResourceInterface
     */
    public function getByModel(Model $model);

    /**
     * Returns resource for given model class, if available.
     *
     * @param string $modelClass
     * @return ResourceInterface|null
     */
    public function getByModelClass($modelClass);

}
