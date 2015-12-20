<?php
namespace Czim\JsonApi\Requests;

use Czim\JsonApi\DataObjects;
use InvalidArgumentException;

/**
 * To use this, make sure a (protected) property $jsonApiContent
 * is available with an instance of DataObjects\Main
 */
trait JsonApiDataAccessorsTrait
{

    /**
     * Returns whether the main JSON-API data contains a single resource
     * (instead of several or none in a list)
     *
     * @return bool
     */
    public function isSingleResource()
    {
        return ($this->jsonApiContent->data instanceof DataObjects\Resource);
    }

    /**
     * Returns data for resource, by index if multiple
     *
     * @param int $index
     * @return DataObjects\Resource|null
     */
    public function getResource($index = 0)
    {
        if (is_null($this->jsonApiContent->data)) return null;

        if ($this->isSingleResource()) {
            return $this->jsonApiContent->data;
        }

        $index = (int) $index;

        if ( ! isset($this->jsonApiContent['data'][ $index ])) {
            throw new InvalidArgumentException("JSON-API Data Resource with index $index is not set");
        }

        return $this->jsonApiContent['data'][ $index ];
    }

    /**
     * Returns top-level resource type
     *
     * @param int $index    index of the resource if not single-resource data
     * @return null|string
     */
    public function getType($index = 0)
    {
        return $this->getDataElementByIndex('type', $index);
    }

    /**
     * Returns top level resource ID
     *
     * @param int $index    index of the resource if not single-resource data
     * @return string|null
     */
    public function getId($index = 0)
    {
        return $this->getDataElementByIndex('id', $index);
    }

    /**
     * Returns top level relationships
     *
     * @param int $index    index of the resource if not single-resource data
     * @return DataObjects\Relationship[]
     */
    public function getRelationships($index = 0)
    {
        return $this->getDataElementByIndex('relationships', $index, []);
    }

    /**
     * Returns a relationship by name/key
     *
     * @param string $key       name of the relationship to retrieve
     * @param int    $index     index of the resource if not single-resource data
     * @return DataObjects\Relationship|null
     */
    public function getRelationship($key, $index = 0)
    {
        $relationships = $this->getRelationships($index);

        if (is_null($relationships) || ! array_key_exists($key, $relationships)) return null;

        return $relationships[$key];
    }

    /**
     * Returns attributes from the data object of the json api object
     *
     * @param int $index    index of the resource if not single-resource data
     * @return DataObjects\Attributes|null
     */
    public function getAttributes($index = 0)
    {
        return $this->getDataElementByIndex('attributes', $index);
    }

    /**
     * Returns attributes from the data object of the json api object
     *
     * @param string $key           attribute key name
     * @param int    $index         index of the resource if not single-resource data
     * @param bool   $dotNotation   whether to apply the attribute key in dot notation
     * @return mixed|null
     */
    public function getAttribute($key, $index = 0, $dotNotation = true)
    {
        $attributes = $this->getAttributes();

        if (is_null($attributes)) return null;

        /** @var \Czim\JsonApi\DataObjects\Attributes $attributes */

        if ($dotNotation) {
            return $attributes->getNested($key);
        }

        return $attributes->getAttribute($key);
    }

    /**
     * Returns included resources from the json api object
     *
     * @return DataObjects\Resource[]
     */
    public function getAllIncluded()
    {
        return $this->jsonApiContent->included ?: [];
    }

    /**
     * Returns an included resource (set) by key from the json api object
     *
     * @param string $key   the key of the included data
     * @return DataObjects\Resource[]
     */
    public function getIncluded($key = null)
    {
        if ( ! $this->jsonApiContent->included) {
            return null;
        }

        return $this->jsonApiContent->included[$key];
    }

    /**
     * Returns whether the json api object has listed errors
     *
     * @return bool
     */
    public function hasErrors()
    {
        return (count($this->jsonApiContent->errors ?: []) > 0);
    }

    /**
     * Returns errors from the json api object
     *
     * @return DataObjects\Error[]
     */
    public function getErrors()
    {
        return $this->jsonApiContent->errors ?: [];
    }

    /**
     * Returns first errors from the json api object, if there are any
     *
     * @return DataObjects\Error|null
     */
    public function getFirstError()
    {
        return head($this->jsonApiContent->errors ?: []);
    }

    /**
     * Returns meta from the data object of the json api object
     *
     * @return DataObjects\Meta|null
     */
    public function getMeta()
    {
        return $this->jsonApiContent->meta;
    }

    /**
     * Returns json-api section from the data object of the json api object
     *
     * @return DataObjects\JsonApi|null
     */
    public function getJsonApiMeta()
    {
        return $this->jsonApiContent->jsonapi;
    }

    /**
     * @param string $key
     * @param int    $index     index of the resource if not single-resource data
     * @param null   $default   what to return if empty
     * @return mixed|null
     */
    protected function getDataElementByIndex($key, $index = 0, $default = null)
    {
        if (is_null($this->jsonApiContent->data)) return $default;

        if ($this->isSingleResource()) {

            return $this->jsonApiContent->data[ $key ] ?: $default;
        }

        $index = (int) $index;

        if ( ! isset($this->jsonApiContent['data'][ $index ])) {

            throw new InvalidArgumentException("JSON-API Data Resource with index $index is not set");
        }

        return $this->jsonApiContent['data'][ $index ][ $key ] ?: $default;
    }

}
