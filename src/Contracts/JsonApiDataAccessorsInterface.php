<?php
namespace Czim\JsonApi\Contracts;

use Czim\JsonApi\DataObjects;

interface JsonApiDataAccessorsInterface
{

    /**
     * Returns whether the main JSON-API data contains a single resource
     * (instead of several)
     *
     * @return bool
     */
    public function isSingleResource();

    /**
     * Returns data for resource, by index if multiple
     *
     * @param int $index
     * @return DataObjects\Resource|null
     */
    public function getResource($index = 0);

    /**
     * Returns top-level resource type
     *
     * @param int $index    index of the resource if not single-resource data
     * @return null|string
     */
    public function getType($index = 0);

    /**
     * Returns top level resource ID
     *
     * @param int $index    index of the resource if not single-resource data
     * @return string|null
     */
    public function getId($index = 0);

    /**
     * Returns top level relationships
     *
     * @param int $index    index of the resource if not single-resource data
     * @return DataObjects\Relationship[]
     */
    public function getRelationships($index = 0);

    /**
     * Returns attributes from the data object of the json api object
     *
     * @param int $index    index of the resource if not single-resource data
     * @return DataObjects\Attributes|null
     */
    public function getAttributes($index = 0);

    /**
     * Returns included resources from the json api object
     *
     * @return DataObjects\Resource[]
     */
    public function getIncluded();

    /**
     * Returns whether the json api object has listed errors
     *
     * @return bool
     */
    public function hasErrors();

    /**
     * Returns errors from the json api object
     *
     * @return DataObjects\Error[]
     */
    public function getErrors();

    /**
     * Returns first errors from the json api object, if there are any
     *
     * @return DataObjects\Error|null
     */
    public function getFirstError();

    /**
     * Returns meta from the data object of the json api object
     *
     * @return DataObjects\Meta|null
     */
    public function getMeta();

    /**
     * Returns json-api section from the data object of the json api object
     *
     * @return DataObjects\JsonApi|null
     */
    public function getJsonApiMeta();

}
