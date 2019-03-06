<?php
namespace Czim\JsonApi\Data;

/**
 * Class Links
 */
class Links extends AbstractDataObject
{

    /**
     * Converts attributes to specific dataobjects where relevant.
     *
     * @param string $key
     * @return string|Link
     */
    public function &getAttributeValue(string $key)
    {
        if ( ! isset($this->attributes[$key])) {
            $null = null;
            return $null;
        }

        if (is_string($this->attributes[$key])) {
            return $this->attributes[$key];
        }

        if ( ! ($this->attributes[$key] instanceof Link)) {
            $this->attributes[ $key ] = $this->makeNestedDataObject(
                Link::class,
                (array) $this->attributes[ $key ],
                $key
            );
        }

        return $this->attributes[$key];
    }

}
