<?php
namespace Czim\JsonApi\Data;

/**
 * Class Relationships
 */
class Relationships extends AbstractDataObject
{

    /**
     * {@inheritdoc}
     */
    public function &getAttributeValue($key)
    {
        if ( ! isset($this->attributes[$key])) {
            $null = null;
            return $null;
        }

        if ( ! ($this->attributes[$key] instanceof Relationship)) {
            $this->attributes[ $key ] = $this->makeNestedDataObject(
                Relationship::class,
                (array) $this->attributes[ $key ],
                $key
            );
        }

        return $this->attributes[$key];
    }

}
