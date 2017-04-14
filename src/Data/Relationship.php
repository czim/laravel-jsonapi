<?php
namespace Czim\JsonApi\Data;

/**
 * Class Relationship
 *
 * @property Resource|Resource[]|null $data
 * @property Links                    $links
 * @property Meta                     $meta
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
    public function &getAttributeValue($key)
    {
        if ($key === 'data') {

            if ($this->attributes[$key] instanceof Resource) {
                return $this->attributes[$key];
            }

            if (is_array($this->attributes[$key])) {

                // The primary data may be either a single resoure (identifier),
                // or an array of them (or null)
                if (array_key_exists('type', $this->attributes[$key])) {

                    $this->attributes[$key] = $this->makeNestedDataObject(
                        Resource::class,
                        $this->attributes[$key],
                        'data'
                    );

                    return $this->attributes[$key];

                } else {

                    foreach ($this->attributes[$key] as $index => &$item) {

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
