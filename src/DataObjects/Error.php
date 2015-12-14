<?php
namespace Czim\JsonApi\DataObjects;

use Czim\DataObject\AbstractDataObject;

/**
 * @property string      $id
 * @property string      $status
 * @property string      $code
 * @property string      $title
 * @property ErrorSource $source
 * @property Link[]      $links
 * @property Meta        $meta
 */
class Error extends AbstractDataObject
{

    /**
     * Construct with attributes
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        if (isset($attributes['links'])) {
            foreach ($attributes['links'] as $key => $link) {
                if (is_string($link)) {
                    $link = [ 'href' => $link ];
                }
                $attributes['links'][ $key ] = new Link($link);
            }
        }

        if (isset($attributes['source'])) {
            $attributes['source'] = new ErrorSource($attributes['source']);
        }

        if (isset($attributes['meta'])) {
            $attributes['meta'] = new Meta($attributes['meta']);
        }

        parent::__construct($attributes);
    }

}
