<?php
namespace Czim\JsonApi\Encoding;

use Czim\JsonApi\Contracts\SchemaProviderInterface;

/**
 * Schema Provider that always returns an empty array as mapping
 * This is the default 'provider' for cases where mapping is auto-generated
 * by the encoder (ie. mapping is 1-1 on the resource objects being encoded).
 */
class NullSchemaProvider implements SchemaProviderInterface
{

    /**
     * Returns schema mapping usable with the Neomerx JSON-API package
     *
     * @return array
     */
    public function getSchemaMapping()
    {
        return [];
    }

}
