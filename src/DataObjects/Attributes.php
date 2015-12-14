<?php
namespace Czim\JsonApi\DataObjects;

use Czim\DataObject\AbstractDataObject;

class Attributes extends AbstractDataObject
{

    /**
     * Construct with attributes
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        // normalize attribute keys to snake case syntax
        foreach (array_keys($attributes) as $key) {

            $normalizedKey = str_replace('-', '_', $key);

            if ($normalizedKey === $key) continue;

            $attributes[ $normalizedKey ] = $attributes[ $key ];

            unset( $attributes[ $key ] );
        }

        parent::__construct($attributes);
    }

}
