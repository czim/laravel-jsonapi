<?php
namespace Czim\JsonApi\Test\Helpers\Resources\AbstractTest;

use Czim\JsonApi\Support\Resource\AbstractJsonApiResource;

abstract class AbstractTestResource extends AbstractJsonApiResource
{

    /**
     * Returns the JSON-API type.
     *
     * @return string
     */
    public function type()
    {
        return null;
    }

    /**
     * Returns the JSON-API ID.
     *
     * @return string
     */
    public function id()
    {
        return null;
    }

    /**
     * Returns an attribute value.
     *
     * @param string $name attribute name or key
     * @param mixed  $default
     * @return mixed
     */
    public function attributeValue($name, $default = null)
    {
        return null;
    }

    /**
     * Returns reference-only data for relationship include key.
     *
     * @param string $include
     * @return array|array[]|null
     */
    public function relationshipReferences($include)
    {
        return null;
    }

    /**
     * Returns full data for relationship include key.
     *
     * @param string $include
     * @return mixed
     */
    public function relationshipData($include)
    {
        return null;
    }

    /**
     * Returns the JSON-API type for a given include
     *
     * @param string $include
     * @return null|string
     */
    public function relationshipType($include)
    {
        return null;
    }

    /**
     * Returns whether a given include belongs to a singular relationship.
     *
     * @param string $include
     * @return bool
     */
    public function isRelationshipSingular($include)
    {
        return true;
    }

    /**
     * Returns whether a given include belongs to a relationship with variable content.
     *
     * @param string $include
     * @return bool
     */
    public function isRelationshipVariable($include)
    {
        return false;
    }
    
}
