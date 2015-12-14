<?php
namespace Czim\JsonApi\DataObjects;

use Czim\DataObject\AbstractDataObject;
use Illuminate\Support\Str;

/**
 * @property string         $type
 * @property string         $id
 * @property Attributes     $attributes
 * @property Relationship[] $relationships
 * @property Link[]         $links
 * @property Meta           $meta
 */
class Resource extends AbstractDataObject
{

    /**
     * Construct with attributes
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        if (isset($attributes['attributes'])) {
            $attributes['attributes'] = new Attributes($attributes['attributes']);
        }

        // normalize relationship names to camel case
        if (isset($attributes['relationships'])) {
            foreach ($attributes['relationships'] as $name => $relationship) {

                $normalizedName = Str::camel($name);

                $attributes['relationships'][ $normalizedName ] = new Relationship($relationship);

                if ($normalizedName === $name) continue;

                unset( $attributes['relationships'][ $name ] );
            }
        }

        if (isset($attributes['links'])) {
            foreach ($attributes['links'] as $key => $link) {
                $attributes['links'][ $key ] = new Link($link);
            }
        }

        if (isset($attributes['meta'])) {
            $attributes['meta'] = new Meta($attributes['meta']);
        }

        parent::__construct($attributes);
    }

}
