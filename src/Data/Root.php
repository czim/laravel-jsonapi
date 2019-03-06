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
                            // @codeCoverageIgnoreStart
                            continue;
                            // @codeCoverageIgnoreEnd
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
    public function hasData(): bool
    {
        return array_key_exists('data', $this->attributes);
    }

    /**
     * Returns whether the errors key is set.
     *
     * @return bool
     */
    public function hasErrors(): bool
    {
        return array_key_exists('errors', $this->attributes);
    }

    /**
     * Returns whether the included key is set.
     *
     * @return bool
     */
    public function hasIncluded(): bool
    {
        return array_key_exists('included', $this->attributes);
    }

    /**
     * Returns whether the jsonapi key is set.
     *
     * @return bool
     */
    public function hasJsonApi(): bool
    {
        return array_key_exists('jsonapi', $this->attributes);
    }

    /**
     * Returns whether the links key is set.
     *
     * @return bool
     */
    public function hasLinks(): bool
    {
        return array_key_exists('links', $this->attributes);
    }

    /**
     * Returns whether the meta key is set.
     *
     * @return bool
     */
    public function hasMeta(): bool
    {
        return array_key_exists('meta', $this->attributes);
    }

    /**
     * Determines and returns the root type based on available keys.
     *
     * @return string
     * @see RootType
     */
    public function getRootType(): string
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
    public function hasNullData(): bool
    {
        return $this->hasData() && null === $this->attributes['data'];
    }

    /**
     * Returns whether the data key contains a single resource.
     *
     * @return bool
     */
    public function hasSingleResourceData(): bool
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
    public function hasMultipleResourceData(): bool
    {
        if ( ! $this->hasData()) {
            return false;
        }

        return ! $this->hasSingleResourceData();
    }

}
