<?php
namespace Czim\JsonApi\Schema;

use Closure;
use InvalidArgumentException;
use Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaFactoryInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaProviderInterface;
use Neomerx\JsonApi\Factories\Exceptions;

/**
 * Extended to make it possible to set a resource directly when
 * retrieving the schema.
 */
class Container extends \Neomerx\JsonApi\Schema\Container
{

    /**
     * @var array
     */
    private $providerMapping = [];

    /**
     * @var array
     */
    private $createdProviders = [];

    /**
     * @var array
     */
    private $resourceType2Type = [];

    /**
     * @var SchemaFactoryInterface
     */
    private $factory;

    /**
     * @param SchemaFactoryInterface $factory
     * @param array                  $schemas
     */
    public function __construct(SchemaFactoryInterface $factory, array $schemas = [])
    {
        $this->factory = $factory;
        $this->registerArray($schemas);

        // do not call the parent, or duplicate schema registration will occur
        //parent::__construct($factory, $schemas);
    }

    /**
     * Register provider for resource type.
     *
     * @param string         $type
     * @param string|Closure $schema
     *
     * @return void
     */
    public function register($type, $schema)
    {

        // Type must be non-empty string
        $isOk = (is_string($type) === true && empty($type) === false);
        if ($isOk === false) {
            throw new InvalidArgumentException('Type must be non-empty string.');
        }

        $isOk = ((is_string($schema) === true && empty($schema) === false) || $schema instanceof Closure);

        if ($isOk === false) {
            throw new InvalidArgumentException("Schema for type '{$type}' must be non-empty string or Closure.");
        }

        if (isset($this->providerMapping[$type]) === true) {
            throw new InvalidArgumentException("Type should not be used more than once to register a schema ('{$type}').");
        }

        $this->providerMapping[$type] = $schema;
    }

    /**
     * Register providers for resource types.
     *
     * @param array $schemas
     *
     * @return void
     */
    public function registerArray(array $schemas)
    {
        foreach ($schemas as $type => $schema) {
            $this->register($type, $schema);
        }
    }

    /**
     * @inheritdoc
     */
    public function getSchema($resource)
    {
        return parent::getSchema($resource)->setResource($resource);
    }

    /**
     * @inheritdoc
     */
    public function getSchemaByType($type)
    {
        is_string($type) === true ?: Exceptions::throwInvalidArgument('type', $type);

        if (isset($this->createdProviders[$type])) {
            return $this->createdProviders[$type];
        }

        if (isset($this->providerMapping[$type]) === false) {

            // todo make this better and less yolo
            // inject standard schema
            $this->providerMapping[$type] = function (SchemaFactoryInterface $factory, ContainerInterface $container) use ($type) {
                return new ResourceSchema($factory, $container, $type);
            };


            //throw new InvalidArgumentException("Schema is not registered for type '{$type}'.");
        }

        $classNameOrClosure = $this->providerMapping[$type];

        if ($classNameOrClosure instanceof Closure) {
            $this->createdProviders[$type] = ($schema = $classNameOrClosure($this->factory, $this));
        } else {
            $this->createdProviders[$type] = ($schema = new $classNameOrClosure($this->factory, $this));
        }

        /** @var SchemaProviderInterface $schema */

        $this->resourceType2Type[ $schema->getResourceType() ] = $type;

        return $schema;
    }

    /**
     * @inheritdoc
     */
    public function getSchemaByResourceType($resourceType)
    {
        // Schema is not found among instantiated schemas for resource type $resourceType
        $isOk = (is_string($resourceType) === true && isset($this->resourceType2Type[$resourceType]) === true);

        // Schema might not be found if it hasn't been searched by type (not resource type) before.
        // We instantiate all schemas and then find one.
        if ($isOk === false) {

            //// todo make this better and less yolo
            //// inject standard schema
            //$this->providerMapping[$resourceType] = function (SchemaFactoryInterface $factory, ContainerInterface $container) use ($resourceType) {
            //    return new ResourceSchema($factory, $container, $resourceType);
            //};

            foreach ($this->providerMapping as $type => $schema) {
                if (isset($this->createdProviders[$type]) === false) {
                    // it will instantiate the schema
                    $this->getSchemaByType($type);
                }
            }
        }

        // search one more time
        $isOk = (is_string($resourceType) === true && isset($this->resourceType2Type[$resourceType]) === true);

        if ($isOk === false) {
            throw new InvalidArgumentException(T::t(
                'Schema is not registered for resource type \'%s\'.',
                [$resourceType]
            ));
        }

        return $this->getSchemaByType(
            $this->resourceType2Type[ $resourceType ]
        );
    }

}
