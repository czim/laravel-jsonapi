<?php
namespace Czim\JsonApi\Data;

/**
 * Class Relationship
 *
 * @property \Czim\JsonApi\Data\Resource|\Czim\JsonApi\Data\Resource[]|null $data
 * @property Links $links
 * @property Meta  $meta
 */
class Relationship extends AbstractDataObject
{

    protected $objects = [
        'links' => Links::class,
        'meta'  => Meta::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function &getAttributeValue(string $key)
    {
        if ($key === 'data') {

            if ($this->attributes['data'] instanceof Resource) {
                return $this->attributes['data'];
            }

            if (is_array($this->attributes['data'])) {

                // The primary data may be either a single resoure (identifier),
                // or an array of them (or null)
                if (array_key_exists('type', $this->attributes['data'])) {

                    $this->attributes['data'] = $this->makeNestedDataObject(
                        Resource::class,
                        $this->attributes['data'],
                        'data'
                    );

                    return $this->attributes['data'];

                } else {

                    foreach ($this->attributes['data'] as $index => &$item) {

                        if (null === $item) {
                            continue;
                        }

                        if ( ! is_a($item, Resource::class)) {
                            $item = $this->makeNestedDataObject(Resource::class, $item, 'data.' . $index);
                        }
                    }

                    unset($item);
                }
            }
        }

        return parent::getAttributeValue($key);
    }

}
