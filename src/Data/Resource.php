<?php
namespace Czim\JsonApi\Data;

/**
 * Class Resource
 *
 * @property string         $id
 * @property string         $type
 * @property Attributes     $attributes
 * @property Relationships  $relationships
 * @property Links          $links
 * @property Meta           $meta
 */
class Resource extends AbstractDataObject
{

    protected $objects = [
        'attributes'    => Attributes::class . '!',
        'relationships' => Relationships::class . '!',
        'links'         => Links::class . '!',
        'meta'          => Meta::class . '!',
    ];

    /**
     * Returns whether the attributes key is set.
     *
     * @return bool
     */
    public function hasAttributes()
    {
        return array_key_exists('attributes', $this->attributes);
    }

    /**
     * Returns whether the relationships key is set.
     *
     * @return bool
     */
    public function hasRelationships()
    {
        return array_key_exists('relationships', $this->attributes);
    }

    /**
     * Returns whether the links key is set.
     *
     * @return bool
     */
    public function hasLinks()
    {
        return array_key_exists('links', $this->attributes);
    }

    /**
     * Returns whether the meta key is set.
     *
     * @return bool
     */
    public function hasMeta()
    {
        return array_key_exists('meta', $this->attributes);
    }

    /**
     * Returns whether this resource is a type+id identifier only.
     *
     * @return bool
     */
    public function isResourceIdentifier()
    {
        return  array_key_exists('id', $this->attributes)
            &&  array_key_exists('type', $this->attributes)
            &&  ! $this->hasAttributes()
            &&  ! $this->hasRelationships();
    }
    
}
