<?php
namespace Czim\JsonApi\Support\Resource;

use Czim\JsonApi\Contracts\Resource\ResourceInterface;

abstract class AbstractJsonApiResource implements ResourceInterface
{

    /**
     * @var string[]
     */
    protected $availableAttributes = [];

    /**
     * @var string[]
     */
    protected $availableIncludes = [];

    /**
     * @var string[]
     */
    protected $defaultIncludes = [];

    /**
     * Whether type/id references should be included for availableIncludes.
     *
     * Set to false to only use links for relations; set to true to always include
     * type/id references. Alternatively, set as an array of strings to whitelist
     * the relations that should have type/id references.
     *
     * Type/id references are always included for relations that are actually included,
     * either by the user, or by setting them in $defaultIncludes.
     *
     * @var bool|string[]
     */
    protected $includeReferences = true;

    /**
     * Whether specific includes should not get type/id references.
     *
     * This is the complement for $includeReferences. It is ignored unless filled,
     * and any excluded references take precendence over set inclusions (whether specific
     * or all-inclusive).
     *
     * @var string[]
     */
    protected $excludeReferences = [];

    /**
     * @var string[]
     */
    protected $availableFilters = [];

    /**
     * @var string[]
     */
    protected $defaultFilters = [];

    /**
     * @var string[]
     */
    protected $availableSortAttributes = [];

    /**
     * @var string[]
     */
    protected $defaultSortAttributes = [];


    /**
     * Returns the JSON-API type.
     *
     * @return string
     */
    abstract public function type();

    /**
     * Returns the JSON-API ID.
     *
     * @return string
     */
    abstract public function id();

    /**
     * Returns an attribute value.
     *
     * @param string $name attribute name or key
     * @param mixed  $default
     * @return mixed
     */
    abstract public function attributeValue($name, $default = null);

    /**
     * Returns reference-only data for relationship include key.
     *
     * @param string $include
     * @return array|array[]|null
     */
    abstract public function relationshipReferences($include);

    /**
     * Returns full data for relationship include key.
     *
     * @param string $include
     * @return mixed
     */
    abstract public function relationshipData($include);

    /**
     * Returns whether a given include belongs to a singular relationship.
     *
     * @param string $include
     * @return bool
     */
    abstract public function isRelationshipSingular($include);

    /**
     * Returns whether a given include belongs to a relationship with variable content.
     *
     * @param string $include
     * @return bool
     */
    abstract public function isRelationshipVariable($include);

    /**
     * Returns list of attributes to include by key.
     *
     * These may be direct attributes on the model, or they may
     * have decorators/accessors on the resource.
     *
     * @return string[]
     */
    public function availableAttributes()
    {
        return $this->availableAttributes;
    }

    /**
     * Returns a list of available includes.
     *
     * These may be key-value pairs, where the key is the include name to use in the request,
     * and the value is the relation method on the model.
     *
     * If only a string value is given, instead of a key-value pair, it is used both as the
     * key as well as the value.
     *
     * @return string[]
     */
    public function availableIncludes()
    {
        return $this->availableIncludes;
    }

    /**
     * Returns a list of includes that should be included by default.
     *
     * @return string[]
     */
    public function defaultIncludes()
    {
        return $this->defaultIncludes;
    }

    /**
     * Returns whether type references should be included for a given include relation by name/key.
     *
     * @param string $name
     * @return bool
     */
    public function includeReferencesForRelation($name)
    {
        if (count($this->excludeReferences) && array_intersect($this->excludeReferences, [ $name ])) {
            return false;
        }

        if (is_array($this->includeReferences)) {
            return (bool) count(array_intersect($this->includeReferences, [ $name ]));
        }

        return (bool) $this->includeReferences;
    }

    /**
     * Returns list of attribute keys that may be filtered.
     *
     * @return string[]
     */
    public function availableFilters()
    {
        return $this->availableFilters;
    }

    /**
     * Returns optional default filter values to apply.
     *
     * These may be overridden by user defined values, if they are also present in the filterAttributes.
     *
     * @return null|array
     */
    public function defaultFilters()
    {
        return $this->defaultFilters;
    }

    /**
     * Returns list of sortable attribute keys.
     *
     * @return string[]
     */
    public function availableSortAttributes()
    {
        return $this->availableSortAttributes;
    }

    /**
     * Returns default sort definition.
     *
     * @return string|string[]
     */
    public function defaultSortAttributes()
    {
        return $this->defaultSortAttributes;
    }

    /**
     * Returns optional meta section.
     *
     * @return array|null   ignored if null
     * @codeCoverageIgnore
     */
    public function getMeta()
    {
        return null;
    }

}
