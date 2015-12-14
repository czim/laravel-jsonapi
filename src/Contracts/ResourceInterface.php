<?php
namespace Czim\JsonApi\Contracts;

interface ResourceInterface
{

    /**
     * Returns the URL path to the resource (relative)
     *
     * @return string
     */
    public function getResourcePath();

    /**
     * Returns the name of the resource
     *
     * @return string
     */
    public function getResourceName();

    /**
     * Returns the ID for the resource
     *
     * @return string
     */
    public function getResourceId();

    /**
     * Returns the attributes of the resource
     *
     * @return mixed[]
     */
    public function getResourceAttributes();

    /**
     * Returns the related content for the resource
     *
     * @param bool $showEmpty   whether to show and include relations with null content
     * @return mixed[]
     */
    public function getResourceRelations($showEmpty = false);

    /**
     * Returns resource type to use for encoder
     *
     * @return string
     */
    public function getResourceType();

    /**
     * Returns resource sub-url to use for encoder
     *
     * @return string
     */
    public function getResourceSubUrl();

}
