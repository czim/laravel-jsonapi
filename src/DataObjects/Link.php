<?php
namespace Czim\JsonApi\DataObjects;

use Czim\DataObject\AbstractDataObject;

/**
 * @property string $href
 * @property Meta   $meta
 */
class Link extends AbstractDataObject
{

    /**
     * Construct with attributes
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        if (isset($attributes['meta'])) {
            $attributes['meta'] = new Meta($attributes['meta']);
        }

        parent::__construct($attributes);
    }

    /**
     * Returns only HREF part as string
     *
     * Note that this means that any meta data will be disregarded
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getAttribute('href') ?: '';
    }

    /**
     * @return array|string
     */
    public function toArray()
    {
        // make simple version if there is no meta data set
        if (empty($this->getAttribute('meta'))) {
            return $this->getAttribute('href') ?: '';
        }

        return parent::toArray();
    }

}
