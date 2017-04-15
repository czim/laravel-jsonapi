<?php
namespace Czim\JsonApi\Repositories;

use Czim\JsonApi\Contracts\Repositories\ResourceCollectorInterface;
use Czim\JsonApi\Contracts\Repositories\ResourceRepositoryInterface;
use Czim\JsonApi\Contracts\Resource\ResourceInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class ResourceRepository implements ResourceRepositoryInterface
{

    /**
     * @var ResourceCollectorInterface
     */
    protected $collector;

    /**
     * Registered resources.
     *
     * @var Collection|ResourceInterface
     */
    protected $resources;

    /**
     * Whether the resources have been registered.
     *
     * This is used to perform lazy loading of resources.
     *
     * @var bool
     */
    protected $initialized = false;

    /**
     * Map of (type) keys for resources, keyed by classes.
     *
     * @var string[]
     */
    protected $classMap = [];


    /**
     * @param ResourceCollectorInterface $collector
     */
    public function __construct(ResourceCollectorInterface $collector)
    {
        $this->collector = $collector;

        $this->resources = new Collection;
    }


    /**
     * Initializes repository, registering resources where possible.
     */
    public function initialize()
    {
        if ($this->initialized) {
            return;
        }

        $resources = $this->collector->collect();

        if ( ! $this->resources->isEmpty()) {
            $this->resources = $this->resources->merge($resources);
        } else {
            $this->resources = $resources;
        }

        $this->generateClassMap();

        $this->initialized = true;
    }

    /**
     * Registers a resource instance for a given model or model class.
     *
     * This will overwrite any previous resource assigned for the model,
     * regardless of whether this is done before or after initialization.
     *
     * @param Model|string             $model
     * @param ResourceInterface|string $resource
     * @return $this
     */
    public function register($model, $resource)
    {
        if ($model instanceof Model) {
            $model = get_class($model);
        }

        if ( ! ($resource instanceof ResourceInterface)) {
            $resource = $this->instantiateResource($resource);
        }

        // Resource must have a model set before type() is guaranteed to work.
        if ( ! $resource->getModel()) {
            $resource->setModel( new $model );
        }

        $type = $resource->type();

        $this->resources->put($type, $resource);

        $this->classMap[ $model ] = $type;

        return $this;
    }

    /**
     * Returns all registered resources.
     *
     * @return Collection|ResourceInterface[]
     */
    public function getAll()
    {
        $this->initialize();

        return $this->resources;
    }

    /**
     * Returns resource for JSON-API type, if available.
     *
     * @param string $type
     * @return ResourceInterface|null
     */
    public function getByType($type)
    {
        $this->initialize();

        if ($resource = $this->resources->get($type)) {
            return clone $resource;
        }

        return null;
    }

    /**
     * Returns resource for given model instance, if available.
     *
     * @param Model $model
     * @return null|ResourceInterface
     */
    public function getByModel(Model $model)
    {
        return $this->getByModelClass(get_class($model));
    }

    /**
     * Returns resource for given model class, if available.
     *
     * @param string $modelClass
     * @return ResourceInterface|null
     */
    public function getByModelClass($modelClass)
    {
        $this->initialize();

        if ( ! array_key_exists($modelClass, $this->classMap)) {
            return null;
        }

        return $this->getByType($this->classMap[ $modelClass ]);
    }

    /**
     * Generates a fresh class map.
     */
    protected function generateClassMap()
    {
        $this->classMap = [];

        foreach ($this->resources as $type => $resource) {

            if ( ! $resource->getModel()) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }

            $this->classMap[ get_class($resource->getModel()) ] = $type;
        }
    }

    /**
     * Makes an instance of a resource by FQN.
     *
     * @param string $class
     * @return ResourceInterface
     */
    protected function instantiateResource($class)
    {
        $resource = app($class);

        if ( ! ($resource instanceof ResourceInterface)) {
            throw new InvalidArgumentException("{$class} does not implement ResourceInterface");
        }

        return $resource;
    }

}
