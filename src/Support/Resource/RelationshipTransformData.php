<?php
namespace Czim\JsonApi\Support\Resource;

use Czim\DataObject\AbstractDataObject;
use Czim\JsonApi\Contracts\Resource\ResourceInterface;

/**
 * Data for transforming a model/resource's relationship.
 *
 * @property ResourceInterface $resource
 * @property string            $include      include key for relation
 * @property bool              $references   whether the type/id references should be included (when not including full)
 * @property bool              $sideload     whether to retrieve & sideload full data for the include
 */
class RelationshipTransformData extends AbstractDataObject
{
    /**
     * @var array
     */
    protected $attributes = [
        'references' => true,
        'sideload'   => true,
    ];
}

