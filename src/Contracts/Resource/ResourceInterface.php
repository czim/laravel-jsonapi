<?php
namespace Czim\JsonApi\Contracts\Resource;

interface ResourceInterface
{

    /**
     * Returns the JSON-API type.
     *
     * @return string
     */
    public function type();

    /**
     * Returns the JSON-API ID.
     *
     * @return string
     */
    public function id();


    /**
     * Returns list of attributes to include by key.
     *
     * These may be direct attributes on the model, or they may
     * have decorators/accessors on the resource.
     *
     * @return string[]
     */
    public function availableAttributes();

    /**
     * Returns an attribute value, directly from the model, or decorated for the resource.
     *
     * @param string $name      attribute name or key
     * @param mixed  $default
     * @return mixed
     */
    public function attributeValue($name, $default = null);

    /**
     * Returns reference-only data for relationship include key.
     *
     * @param string $include
     * @return array|array[]|null
     */
    public function relationshipReferences($include);

    /**
     * Returns full data for relationship include key.
     *
     * @param string $include
     * @return mixed
     */
    public function relationshipData($include);

    /**
     * Returns the JSON-API type for a given include
     *
     * @param string $include
     * @return null|string
     */
    public function relationshipType($include);

    /**
     * Returns whether a given include belongs to a singular relationship.
     *
     * @param string $include
     * @return bool
     */
    public function isRelationshipSingular($include);

    /**
     * Returns whether a given include belongs to a relationship with variable content.
     *
     * @param string $include
     * @return bool
     */
    public function isRelationshipVariable($include);

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
    public function availableIncludes();

    /**
     * Returns a list of includes that should be included by default.
     *
     * @return string[]
     */
    public function defaultIncludes();

    /**
     * Returns whether type references should be included for a given include relation by name/key.
     *
     * @param string $name
     * @return bool
     */
    public function includeReferencesForRelation($name);


    /**
     * Returns list of attribute keys that may be filtered.
     *
     * @return string[]
     */
    public function availableFilters();

    /**
     * Returns optional default filter values to apply.
     *
     * These may be overridden by user defined values, if they are also present in the filterAttributes.
     *
     * @return null|array
     */
    public function defaultFilters();


    /**
     * Returns list of sortable attribute keys.
     *
     * @return string[]
     */
    public function availableSortAttributes();

    /**
     * Returns default sort definition.
     *
     * @return string|string[]
     */
    public function defaultSortAttributes();

    /**
     * Returns optional meta section.
     *
     * @return array|null   ignored if null
     */
    public function getMeta();

}
