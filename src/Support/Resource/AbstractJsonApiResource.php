<?php
namespace Czim\JsonApi\Support\Resource;

use Czim\JsonApi\Contracts\Resource\ResourceInterface;
use Czim\JsonApi\Contracts\Support\Type\TypeMakerInterface;
use Czim\JsonApi\Exceptions\InvalidIncludeException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use RuntimeException;

class AbstractJsonApiResource implements ResourceInterface
{

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var string[]
     */
    protected $availableAttributes = [];

    /**
     * @var string[]
     */
    protected $availableIncludes = [];

    /**
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
     * @var string[]
     */
    protected $availableFilters = [];

    /**
     * @var string[]
     */
    protected $defaultFilters = [];

    /**
     * @var string[]
     */
    protected $availableSortAttributes = [];

    /**
     * @var string[]
     */
    protected $defaultSortAttributes = [];

    /**
     * Optional mapping for includes to Eloquent relation methods.
     *
     * For instance:
     *
     *      'your-include' => 'includeMethod'
     *
     * Would make 'your-include' refer to model->includeMethod() to access the relation.
     *
     * @var string[]    associative, keyed by include key
     */
    protected $includeRelations = [];



    /**
     * Sets the model instance to use.
     *
     * This should be done before calling any other method, unless
     * a model is guaranteed to be set using the constructor.
     *
     * @param Model $model
     * @return $this
     */
    public function setModel(Model $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Returns the model instance used.
     *
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Returns the JSON-API type.
     *
     * @return string
     */
    public function type()
    {
        return $this->getTypeMaker()->makeForModel($this->model);
    }

    /**
     * Returns the JSON-API ID.
     *
     * @return string
     */
    public function id()
    {
        return (string) $this->model->getKey();
    }

    /**
     * Returns an attribute value, directly from the model, or decorated for the resource.
     *
     * @param string $name attribute name or key
     * @param mixed  $default
     * @return mixed
     */
    public function attributeValue($name, $default = null)
    {
        $accessorMethod = 'get' . studly_case($name) . 'Attribute';

        if (method_exists($this, $accessorMethod)) {
            $value = call_user_func([ $this, $accessorMethod ]);
        } else {
            $value = $this->model->{$name};
        }

        return null !== $value ? $value : $default;
    }

    /**
     * Returns the Eloquent relation method for an include key/name, if possible.
     *
     * @param string $name
     * @return Relation|null
     * @throws InvalidIncludeException
     */
    public function includeRelation($name)
    {
        $method = $this->getRelationMethodForInclude($name);

        return $this->getModelRelation($method);
    }

    /**
     * Returns the Eloquent relation method for a given include name.
     *
     * @param string $name
     * @return string
     * @throws InvalidIncludeException
     */
    public function getRelationMethodForInclude($name)
    {
        if ( ! in_array($name, $this->availableIncludes())) {
            throw new InvalidIncludeException("'{$name}' is not a valid include for '" . get_class($this) . "'");
        }

        if (empty($this->includeRelations) || ! array_key_exists($name, $this->includeRelations)) {
            return $name;
        }

        return $this->includeRelations[ $name ];
    }

    /**
     * Returns relation type string for include method name from Eloquent model.
     *
     * @param string $method
     * @return Relation
     */
    protected function getModelRelation($method)
    {
        $model          = $this->getModel();
        $relationMethod = camel_case($method);

        if ( ! method_exists($model, $relationMethod)) {
            throw new RuntimeException(
                "No method '{$relationMethod}' exists on model " . get_class($model) . " for relation '{$method}"
            );
        }

        $relation = $model->{$relationMethod}();

        if ( ! ($relation instanceof Relation)) {
            throw new RuntimeException(
                "Method '{$relationMethod}' on model " . get_class($model) . " is not a relation method"
            );
        }

        return $relation;
    }

    /**
     * Returns list of attributes to include by key.
     *
     * These may be direct attributes on the model, or they may
     * have decorators/accessors on the resource.
     *
     * @return string[]
     */
    public function availableAttributes()
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
    public function availableIncludes()
    {
        return $this->availableIncludes;
    }

    /**
     * Returns a list of includes that should be included by default.
     *
     * @return string[]
     */
    public function defaultIncludes()
    {
        return $this->defaultIncludes;
    }

    /**
     * Returns whether type references should be included for a given include relation by name/key.
     *
     * @param string $name
     * @return bool
     */
    public function includeReferencesForRelation($name)
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
    public function availableFilters()
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
    public function defaultFilters()
    {
        return $this->defaultFilters;
    }

    /**
     * Returns list of sortable attribute keys.
     *
     * @return string[]
     */
    public function availableSortAttributes()
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
    public function getMeta()
    {
        return null;
    }

    /**
     * @return TypeMakerInterface
     */
    protected function getTypeMaker()
    {
        return app(TypeMakerInterface::class);
    }

}
