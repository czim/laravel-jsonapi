<?php
namespace Czim\JsonApi\Support\Resource;

use Czim\DataObject\AbstractDataObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * @property string   $key          resource/relation name or key
 * @property bool     $variable     whether related models may vary in class/type
 * @property bool     $singular     whether the relation is singular
 * @property Relation $relation
 * @property Model    $model        related model, if not variable
 */
class RelationData extends AbstractDataObject
{
    /**
     * @var array
     */
    protected $attributes = [
        'variable' => false,
        'singular' => false,
    ];
}

