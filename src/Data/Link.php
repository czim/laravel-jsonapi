<?php
namespace Czim\JsonApi\Data;

/**
 * Class Link
 *
 * @property string $href
 * @property Meta   $meta
 */
class Link extends AbstractDataObject
{

    protected $objects = [
        'meta' => Meta::class,
    ];

}
