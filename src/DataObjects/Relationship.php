<?php
namespace Czim\JsonApi\DataObjects;

use Czim\DataObject\AbstractDataObject;

/**
 * @property Resource|Resource[] $data
 * @property Link[]              $links
 * @property Meta                $meta
 */
class Relationship extends AbstractDataObject
{

    /**
     * Construct with attributes
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        if (isset($attributes['data'])) {
            if (isset($attributes['data']['type'])) {
                $attributes['data'] = new Resource($attributes['data']);
            } else {
                foreach ($attributes['data'] as $index => $resource) {
                    $attributes['data'][$index] = new Resource($resource);
                }
            }
        }

        if (isset($attributes['links'])) {
            foreach ($attributes['links'] as $key => $link) {
                if (is_string($link)) {
                    $link = [ 'href' => $link ];
                }
                $attributes['links'][ $key ] = new Link($link);
            }
        }

        if (isset($attributes['meta'])) {
            $attributes['meta'] = new Meta($attributes['meta']);
        }

        parent::__construct($attributes);
    }

}
