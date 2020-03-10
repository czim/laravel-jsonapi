<?php
namespace Czim\JsonApi\Support\Resource;

use Carbon\Carbon;
use Czim\JsonApi\Contracts\Resource\ResourceInterface;
use Czim\JsonApi\Contracts\Support\Resource\ResourcePathHelperInterface;
use DateTime;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

abstract class AbstractJsonApiResource implements ResourceInterface
{
    /**
     * The relative path or absolute URL for this resource.
     *
     * If this is not set, a relative path will be based on the namespace of the resource.
     * @see ResourcePathHelper
     *
     * @var null|string
     */
    protected $url;

    /**
     * The attributes to include in the transform.
     *
     * @var string[]
     */
    protected $availableAttributes = [];

    /**
     * The includes that may be used.
     *
     * @var string[]
     */
    protected $availableIncludes = [];

    /**
     * The includes that will be used by default, if they are available.
     *
     * @var string[]
     */
    protected $defaultIncludes = [];

    /**
     * Whether type/id references should be included for availableIncludes.
     *
     * Set to false to only use links for relations; set to true to always include
     * type/id references. Alternatively, set as an array of strings to whitelist
     * the relations that should have type/id references.
     *
     * Type/id references are always included for relations that are actually included,
     * either by the user, or by setting them in $defaultIncludes.
     *
     * @var bool|string[]
     */
    protected $includeReferences = true;

    /**
     * Whether specific includes should not get type/id references.
     *
     * This is the complement for $includeReferences. It is ignored unless filled,
     * and any excluded references take precendence over set inclusions (whether specific
     * or all-inclusive).
     *
     * @var string[]
     */
    protected $excludeReferences = [];

    /**
     * List of filter keys available.
     *
     * @var string[]
     */
    protected $availableFilters = [];

    /**
     * List of filter values to use by default.
     *
     * @var array   associative, keyed by filter key
     */
    protected $defaultFilters = [];

    /**
     * List of sort attribute keys available.
     *
     * @var string[]
     */
    protected $availableSortAttributes = [];

    /**
     * List of sort keys, in order, to use by default.
     * Prefix with '-' to reverse.
     *
     * @var string[]
     */
    protected $defaultSortAttributes = [];

    /**
     * Attribute keys that should be interpreted and formatted as date(time) values.
     *
     * @var array
     */
    protected $dateAttributes = [];

    /**
     * Date formats for attributes.
     *
     * @var array   associative, key is attribute name, value is format string
     */
    protected $dateAttributeFormats = [];


    /**
     * Returns the JSON-API type.
     *
     * @return string
     */
    abstract public function type(): string;

    /**
     * Returns the JSON-API ID.
     *
     * @return string
     */
    abstract public function id(): string;

    /**
     * Returns the full URL for this resource.
     *
     * @return string
     */
    public function url(): string
    {
        $url = $this->url;

        if ($url === null) {
            $url = $this->getResourcePathHelper()->makePath($this);
        }

        if ($this->isUrlAbsolute($url)) {
            return $url;
        }

        return rtrim(config('jsonapi.base_url'), '/') . '/' . ltrim($url);
    }

    /**
     * Returns an attribute value.
     *
     * @param string $name attribute name or key
     * @param mixed  $default
     * @return mixed
     */
    abstract public function attributeValue(string $name, $default = null);

    /**
     * Returns reference-only data for relationship include key.
     *
     * @param string $include
     * @return array|array[]|null
     */
    abstract public function relationshipReferences(string $include): ?array;

    /**
     * Returns full data for relationship include key.
     *
     * @param string $include
     * @return mixed
     */
    abstract public function relationshipData(string $include);

    /**
     * Returns whether a given include belongs to a singular relationship.
     *
     * @param string $include
     * @return bool
     */
    abstract public function isRelationshipSingular(string $include): bool;

    /**
     * Returns whether a given include belongs to a relationship with variable content.
     *
     * @param string $include
     * @return bool
     */
    abstract public function isRelationshipVariable(string $include): bool;

    /**
     * Returns list of attributes to include by key.
     *
     * These may be direct attributes on the model, or they may
     * have decorators/accessors on the resource.
     *
     * @return string[]
     */
    public function availableAttributes(): array
    {
        return $this->availableAttributes;
    }

    /**
     * Returns a list of available includes.
     *
     * These may be key-value pairs, where the key is the include name to use in the request,
     * and the value is the relation method on the model.
     *
     * If only a string value is given, instead of a key-value pair, it is used both as the
     * key as well as the value.
     *
     * @return string[]
     */
    public function availableIncludes(): array
    {
        return $this->availableIncludes;
    }

    /**
     * Returns a list of includes that should be included by default.
     *
     * @return string[]
     */
    public function defaultIncludes(): array
    {
        return $this->defaultIncludes;
    }

    /**
     * Returns whether type references should be included for a given include relation by name/key.
     *
     * @param string $name
     * @return bool
     */
    public function includeReferencesForRelation(string $name): bool
    {
        if (count($this->excludeReferences) && array_intersect($this->excludeReferences, [ $name ])) {
            return false;
        }

        if (is_array($this->includeReferences)) {
            return (bool) count(array_intersect($this->includeReferences, [ $name ]));
        }

        return (bool) $this->includeReferences;
    }

    /**
     * Returns list of attribute keys that may be filtered.
     *
     * @return string[]
     */
    public function availableFilters(): array
    {
        return $this->availableFilters;
    }

    /**
     * Returns optional default filter values to apply.
     *
     * These may be overridden by user defined values, if they are also present in the filterAttributes.
     *
     * @return null|array
     */
    public function defaultFilters(): ?array
    {
        return $this->defaultFilters;
    }

    /**
     * Returns list of sortable attribute keys.
     *
     * @return string[]
     */
    public function availableSortAttributes(): array
    {
        return $this->availableSortAttributes;
    }

    /**
     * Returns default sort definition.
     *
     * @return string|string[]
     */
    public function defaultSortAttributes()
    {
        return $this->defaultSortAttributes;
    }

    /**
     * Returns optional meta section.
     *
     * @return array|null   ignored if null
     * @codeCoverageIgnore
     */
    public function getMeta(): ?array
    {
        return null;
    }

    /**
     * Normalizes attribute names for internal comparison.
     *
     * @param string $name
     * @return string
     */
    protected function normalizeAttributeName(string $name): string
    {
        return Str::snake(str_replace('_', '-', $name), '-');
    }

    /**
     * Returns whether a given attribute should be treated as a datetime value.
     *
     * @param string $name
     * @param mixed  $value
     * @return bool
     */
    protected function isAttributeDate(string $name, $value): bool
    {
        return $value instanceof DateTime || in_array($this->normalizeAttributeName($name), $this->dateAttributes);
    }

    /**
     * Returns the PHP datetime format to use for an attribute, if any.
     *
     * @param string $name
     * @return string|null
     */
    protected function getConfiguredFormatForAttribute(string $name): ?string
    {
        return Arr::get($this->dateAttributeFormats, $this->normalizeAttributeName($name));
    }

    /**
     * Formats a given value in a PHP datetime format.
     *
     * @param string|DateTime $value
     * @param string|null     $format
     * @return string
     */
    protected function formatDate($value, ?string $format = null): string
    {
        $format = $format ?: config('jsonapi.transform.default-datetime-format', 'c');

        if ( ! ($value instanceof DateTime)) {
            $value = new Carbon($value);
        }

        return $value->format($format);
    }

    /**
     * @return ResourcePathHelperInterface
     */
    protected function getResourcePathHelper(): ResourcePathHelperInterface
    {
        return app(ResourcePathHelperInterface::class);
    }

    /**
     * Returns whether a given URL is absolute.
     *
     * @param string $url
     * @return bool
     */
    protected function isUrlAbsolute(string $url): bool
    {
        return (bool) preg_match('#^(https?:)//#', $url);
    }
}
