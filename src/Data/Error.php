<?php
namespace Czim\JsonApi\Data;

/**
 * Class Error
 *
 * @property string      $id
 * @property ErrorLinks  $links
 * @property string      $status
 * @property string      $code
 * @property string      $title
 * @property string      $detail
 * @property ErrorSource $source
 * @property Meta        $meta
 */
class Error extends AbstractDataObject
{

    protected $objects = [
        'links'  => ErrorLinks::class,
        'meta'   => Meta::class,
        'source' => ErrorSource::class,
    ];

}
