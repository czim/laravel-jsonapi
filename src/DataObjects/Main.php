<?php
namespace Czim\JsonApi\DataObjects;

use Czim\DataObject\AbstractDataObject;

/**
 * @property Meta                $meta
 * @property Resource|Resource[] $data
 * @property Link[]              $links
 * @property Resource[]          $included
 * @property Error[]             $errors
 */
class Main extends AbstractDataObject
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
                    $attributes['data'][ $index ] = new Resource($resource);
                }
            }
        }

        if (isset($attributes['included'])) {
            if (isset($attributes['included']['type'])) {
                $attributes['included'] = new Resource($attributes['included']);
            } else {
                foreach ($attributes['included'] as $index => $resource) {
                    $attributes['included'][ $index ] = new Resource($resource);
                }
            }
        }

        if (isset($attributes['errors'])) {
            foreach ($attributes['errors'] as $index => $error) {
                $attributes['errors'][ $index ] = new Error($error);
            }
        }

        if (isset($attributes['meta'])) {
            $attributes['meta'] = new Meta($attributes['meta']);
        }

        if (isset($attributes['jsonapi'])) {
            $attributes['jsonapi'] = new JsonApi($attributes['jsonapi']);
        }

        parent::__construct($attributes);
    }

}
