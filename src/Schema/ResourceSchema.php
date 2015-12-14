<?php
namespace Czim\JsonApi\Schema;

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
        if ( ! is_a($resource, ResourceInterface::class)) {
            throw new InvalidArgumentException('setResource parameter must implement ResourceInterface');
        }

        $this->resourceInstance = $resource;

        $this->resourceType     = $this->resourceInstance->getResourceName();
        $this->selfSubUrl       = $this->resourceInstance->getResourcePath();

        return $this;
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
     * @param object|ResourceInterface $resource
     * @param array                    $includeRelationships    A list of relationships that will be included as full resources.
     * @return array
     */
    public function getRelationships($resource, array $includeRelationships = [])
    {
        // todo what do with $includeRelationships?

        return $resource->getResourceRelations();
    }


    /**
     * The include paths determine what relationship data gets added in the 'included' section.
     *
     * @inheritdoc
     */
    public function getIncludePaths()
    {
        // default is include nothing
        //return [];

        //    // todo rewrite this for standardized approach,
        //    // service provider should somehow dictate/make available the include data
        //    // or use the request (or something sensibly 'global') to read the includes...
        //
        //    if ($this->resourceInstance) {
        //
        //        return array_intersect(
        //            array_map(function ($v) {
        //                return snake_case($v, '-');
        //            }, App::make('jsonapi.include')),
        //            array_keys($this->getRelationships($this->resourceInstance, true))
        //        );
        //    }
        //
        //    return parent::getIncludePaths();

        // test
        return [ 'test-resource-b' ];
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
