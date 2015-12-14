<?php
namespace Czim\JsonApi\DataObjects;

use Czim\DataObject\AbstractDataObject;

/**
 * @property string $version
 * @property Meta   $meta
 */
class JsonApi extends AbstractDataObject
{

    /**
     * Construct with attributes
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        if (isset($attributes['meta'])) {
            $attributes['meta'] = new Meta($attributes['meta']);
        }

        parent::__construct($attributes);
    }

}
