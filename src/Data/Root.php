<?php
namespace Czim\JsonApi\Data;

use Czim\JsonApi\Enums\RootType;

/**
 * Class Root
 *
 * @property null|\Czim\JsonApi\Data\Resource|\Czim\JsonApi\Data\Resource[] $data
 * @property Error[]    $errors
 * @property Resource[] $included
 * @property JsonApi    $jsonapi
 * @property Links      $links
 * @property Meta       $meta
 */
class Root extends AbstractDataObject
{

    protected $objects = [
        'errors'   => Error::class . '[]',
        'included' => Resource::class . '[]',
        'jsonapi'  => JsonApi::class,
        'links'    => Links::class,
        'meta'     => Meta::class,
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

    /**
     * Returns whether the data key is set.
     *
     * @return bool
     */
    public function hasData()
    {
        return array_key_exists('data', $this->attributes);
    }

    /**
     * Returns whether the errors key is set.
     *
     * @return bool
     */
    public function hasErrors()
    {
        return array_key_exists('errors', $this->attributes);
    }

    /**
     * Returns whether the included key is set.
     *
     * @return bool
     */
    public function hasIncluded()
    {
        return array_key_exists('included', $this->attributes);
    }

    /**
     * Returns whether the jsonapi key is set.
     *
     * @return bool
     */
    public function hasJsonApi()
    {
        return array_key_exists('jsonapi', $this->attributes);
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
     * Determines and returns the root type based on available keys.
     *
     * @return string
     * @see RootType
     */
    public function getRootType()
    {
        if ($this->hasData()) {
            return RootType::RESOURCE;
        }

        if ($this->hasErrors()) {
            return RootType::ERROR;
        }

        if ($this->hasMeta()) {
            return RootType::META;
        }

        return RootType::UNKNOWN;
    }

    /**
     *
     * @return bool
     */
    public function hasNullData()
    {
        return $this->hasData() && null === $this->attributes['data'];
    }

    /**
     * Returns whether the data key contains a single resource.
     *
     * @return bool
     */
    public function hasSingleResourceData()
    {
        if ( ! $this->hasData()) {
            return false;
        }

        return $this->data instanceof Resource;
    }

    /**
     * Returns whether the data key contains a list of resources.
     *
     * @return bool
     */
    public function hasMultipleResourceData()
    {
        if ( ! $this->hasData()) {
            return false;
        }

        return ! $this->hasSingleResourceData();
    }

}
