<?php
namespace Czim\JsonApi\Contracts;

interface SchemaProviderInterface
{

    /**
     * Returns schema mapping usable with the Neomerx JSON-API package
     *
     * @return array
     */
    public function getSchemaMapping();

}
