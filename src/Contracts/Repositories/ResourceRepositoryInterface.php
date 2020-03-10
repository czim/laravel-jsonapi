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
    public function initialize(): void;

    /**
     * Registers a resource instance for a given model or model class.
     *
     * @param Model|string             $model       class or instance
     * @param ResourceInterface|string $resource    class or instance
     * @return $this|ResourceRepositoryInterface
     */
    public function register($model, $resource): ResourceRepositoryInterface;

    /**
     * Returns all registered resources.
     *
     * @return Collection|ResourceInterface[]
     */
    public function getAll(): Collection;

    /**
     * Returns resource for JSON-API type, if available.
     *
     * @param string $type
     * @return ResourceInterface|null
     */
    public function getByType(string $type): ?ResourceInterface;

    /**
     * Returns resource for given model instance, if available.
     *
     * @param Model $model
     * @return null|ResourceInterface
     */
    public function getByModel(Model $model): ?ResourceInterface;

    /**
     * Returns resource for given model class, if available.
     *
     * @param string $modelClass
     * @return ResourceInterface|null
     */
    public function getByModelClass(string $modelClass): ?ResourceInterface;
}
