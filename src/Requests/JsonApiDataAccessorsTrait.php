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
     * Returns main data from the json api object
     *
     * @return Resource|Resource[]
     */
    protected function getJsonApiData()
    {
        return $this->jsonApiContent->data;
    }

    /**
     * Returns whether the main JSON-API data contains a single resource
     * (instead of several)
     *
     * @return bool
     */
    public function isSingleResource()
    {
        return ($this->getJsonApiData() instanceof DataObjects\Resource);
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
            return $this->getJsonApiData();
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
        if (is_null($this->jsonApiContent->data)) return null;

        if ($this->isSingleResource()) {
            return $this->jsonApiContent->data['type'];
        }

        $index = (int) $index;

        if ( ! isset($this->jsonApiContent['data'][ $index ])) {
            throw new InvalidArgumentException("JSON-API Data Resource with index $index is not set");
        }

        return $this->jsonApiContent['data'][ $index ]['type'];
    }

    /**
     * Returns top level resource ID
     *
     * @param int $index    index of the resource if not single-resource data
     * @return string|null
     */
    public function getId($index = 0)
    {
        if ($this->isSingleResource()) {
            return $this->jsonApiContent->data['id'];
        }

        $index = (int) $index;

        if ( ! isset($this->jsonApiContent['data'][ $index ])) {
            throw new InvalidArgumentException("JSON-API Data Resource with index $index is not set");
        }

        return $this->jsonApiContent['data'][ $index ]['id'];
    }

    /**
     * Returns top level relationships
     *
     * @param int $index    index of the resource if not single-resource data
     * @return DataObjects\Relationship[]
     */
    public function getRelationships($index = 0)
    {
        if ($this->isSingleResource()) {

            return $this->jsonApiContent->data['relationships'] ?: [];
        }

        $index = (int) $index;

        if ( ! isset($this->jsonApiContent['data'][ $index ])) {
            throw new InvalidArgumentException("JSON-API Data Resource with index $index is not set");
        }

        return $this->jsonApiContent['data'][ $index ]['relationships'] ?: [];
    }

    /**
     * Returns attributes from the data object of the json api object
     *
     * @param int $index    index of the resource if not single-resource data
     * @return DataObjects\Attributes|null
     */
    public function getAttributes($index = 0)
    {
        if ($this->isSingleResource()) {

            return $this->jsonApiContent->data['attributes'] ?: null;
        }

        $index = (int) $index;

        if ( ! isset($this->jsonApiContent['data'][ $index ])) {
            throw new InvalidArgumentException("JSON-API Data Resource with index $index is not set");
        }

        return $this->jsonApiContent['data'][ $index ]['attributes'] ?: null;
    }

}
