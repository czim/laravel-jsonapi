<?php
namespace Czim\JsonApi\Schema;

use Czim\JsonApi\Contracts\JsonApiParametersInterface;
use Czim\JsonApi\Contracts\ResourceInterface;
use InvalidArgumentException;
use Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaFactoryInterface;
use Neomerx\JsonApi\Schema\SchemaProvider;

class ResourceSchema extends SchemaProvider
{

    /**
     * Classname FQN for ResourceInterface object
     *
     * @var string
     */
    protected $resourceClassname;

    /**
     * @var ResourceInterface
     */
    protected $resourceInstance;


    /**
     * @param SchemaFactoryInterface $factory
     * @param ContainerInterface     $container
     * @param string                 $resourceClassname     identifies ResourceInterface object to handle schema for
     */
    public function __construct(SchemaFactoryInterface $factory, ContainerInterface $container, $resourceClassname = null)
    {
        $this->resourceClassname = $resourceClassname;

        // temporary resource instance, just for the resource type & sub url
        // the real resource will be set later, using setResource() calls
        $this->resourceInstance = (new $resourceClassname);

        $this->checkResourceClass($this->resourceInstance);

        $this->resourceType = $this->resourceInstance->getResourceType();
        $this->selfSubUrl   = $this->resourceInstance->getResourceSubUrl();

        parent::__construct($factory, $container);
    }

    /**
     * Stores JSON-API resource instance
     *
     * Called from the Container, on getSchema();
     *
     * @param object|ResourceInterface $resource
     * @return $this
     */
    public function setResource($resource)
    {
        $this->checkResourceClass($resource);

        $this->resourceInstance = $resource;

        $this->resourceType     = $this->resourceInstance->getResourceName();
        $this->selfSubUrl       = $this->resourceInstance->getResourcePath();

        return $this;
    }

    /**
     * Checks whether the resource class is correct
     *
     * @param $resource
     */
    protected function checkResourceClass($resource)
    {
        if ( ! is_a($resource, ResourceInterface::class)) {
            throw new InvalidArgumentException(
                "setResource parameter (" . get_class($resource) . ") must implement ResourceInterface"
            );
        }
    }

    /**
     * Get resource identity.
     *
     * @param object|ResourceInterface $resource
     * @return string
     */
    public function getId($resource)
    {
        return $resource->getResourceId();
    }

    /**
     * Get resource attributes.
     *
     * @param object|ResourceInterface $resource
     * @return array
     */
    public function getAttributes($resource)
    {
        return $resource->getResourceAttributes();
    }

    /**
     * Get resource links.
     *
     * @todo maybe use includeRelationships to load relations on the model
     *
     * @param object|ResourceInterface $resource
     * @param array                    $includeRelationships    A list of relationships that will be included as full resources.
     * @return array
     */
    public function getRelationships($resource, array $includeRelationships = [])
    {
        return $resource->getResourceRelations();
    }


    /**
     * The include paths determine what relationship data gets added in the 'included' section.
     *
     * @inheritdoc
     */
    public function getIncludePaths()
    {
        /** @var JsonApiParametersInterface $parameters */
        $parameters = app(JsonApiParametersInterface::class);

        return $parameters->getIncludePaths();
    }

    /**
     * Overridden so we can set an instance of the resource object
     *
     * @inheritdoc
     */
    public function createResourceObject($resource, $isOriginallyArrayed, $attributeKeysFilter = null)
    {
        $this->setResource($resource);

        return parent::createResourceObject($resource, $isOriginallyArrayed, $attributeKeysFilter);
    }

}
