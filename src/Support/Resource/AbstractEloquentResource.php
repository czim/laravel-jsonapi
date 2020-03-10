<?php
namespace Czim\JsonApi\Support\Resource;

use Czim\JsonApi\Contracts\Repositories\ResourceRepositoryInterface;
use Czim\JsonApi\Contracts\Resource\EloquentResourceInterface;
use Czim\JsonApi\Contracts\Support\Type\TypeMakerInterface;
use Czim\JsonApi\Exceptions\InvalidIncludeException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Str;
use RuntimeException;
use UnexpectedValueException;

abstract class AbstractEloquentResource extends AbstractJsonApiResource implements EloquentResourceInterface
{
    /**
     * @var Model
     */
    protected $model;

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
        return $this->getTypeMaker()->makeForModelClass($this->getModelClass());
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
        $accessorMethod = 'get' . Str::studly(str_replace('-', ' ', $name)) . 'Attribute';

        if (method_exists($this, $accessorMethod)) {
            $value = call_user_func([ $this, $accessorMethod ]);
        } else {
            $value = $this->model->{$this->getModelAttributeForApiAttribute($name)};
        }

        if ($this->isAttributeDate($name, $value)) {
            $value = $this->formatDate($value, $this->getConfiguredFormatForAttribute($name));
        }

        return null !== $value ? $value : $default;
    }

    /**
     * Returns the model attribute for a given JSON-API attribute, if available.
     *
     * @param string $name
     * @return string|false
     */
    public function getModelAttributeForApiAttribute($name)
    {
        return Str::snake($name);
    }

    /**
     * Returns reference-only data for relationship include.
     *
     * @todo deal with variable relation content (variable keys, & json-api types)
     *
     * @param string $include
     * @return array|\array[]|null
     */
    public function relationshipReferences($include)
    {
        $method   = $this->getRelationMethodForInclude($include);
        $relation = $this->getModelRelation($method);
        $singular = $this->isSingularRelation($relation);
        $variable = $this->isVariableRelation($relation);

        $relatedModel = $this->getRelatedModelForRelation($relation);

        // Safeguard: if we cannot determine the model, there may be an issue,
        // or it may simply be an unset morphTo relation.
        if ( ! $relatedModel) {
            if ($variable) {
                return $singular ? null : [];
            }

            // @codeCoverageIgnoreStart
            throw new UnexpectedValueException("Could not determine related model for related reference data lookup");
            // @codeCoverageIgnoreEnd
        }

        $relatedResource = $this->getResourceRepository()->getByModel($relatedModel);

        if ( ! $relatedResource) {
            throw new RuntimeException("Could not determine resource for model '" . get_class($relatedModel) . "'");
        }

        if ($this->model->relationLoaded($method)) {
            if ($singular) {

                if ($this->model->{$method}) {
                    $ids = [ $this->model->{$method}->{$relatedModel->getKeyName()} ];
                } else {
                    $ids = [];
                }

            } else {
                $ids = $this->model->{$method}->pluck($relatedModel->getKeyName())->toArray();
            }

        } else {
            // If the relation is singular, we just need the id so no need to query
            // the entire model.
            if ($relation instanceof Relations\BelongsTo) {
                $foreignKeyName = $relation->getForeignKeyName();
                $id = $this->model->getAttribute($foreignKeyName);

                if ($id !== null) {
                    $ids[] = $id;
                } else {
                    $ids = [];
                }
            } else {
                $ids = $relation->pluck($relatedModel->getQualifiedKeyName())->toArray();
            }
        }

        if ($singular) {
            if ( ! count($ids)) {
                return null;
            }

            return [ 'type' => $relatedResource->type(), 'id' => (string) head($ids) ];
        }

        return array_map(
            function ($id) use ($relatedResource) {
                return [ 'type' => $relatedResource->type(), 'id' => (string) $id ];
            },
            $ids
        );
    }

    /**
     * Returns full data for relationship include.
     *
     * @param string $include
     * @return Model|\Illuminate\Support\Collection|null
     */
    public function relationshipData($include)
    {
        $includeKey = $include;
        $method     = $this->getRelationMethodForInclude($includeKey);

        return $this->getModel()->{$method};
    }

    /**
     * Returns the JSON-API type for a given include.
     *
     * @param string $include
     * @return null|string
     */
    public function relationshipType($include)
    {
        // If the relationship is variable, we can only give a type if it is both singular and filled
        $relation = $this->includeRelation($include);

        if ($this->isVariableRelation($relation)) {
            // @codeCoverageIgnoreStart
            if ( ! $this->isSingularRelation($relation)) {
                return null;
            }

            // Get the type for the actually related item
            /** @var Relations\MorphTo $relation */
            $modelClass = $this->model->{$relation->getMorphType()};

            if ( ! $modelClass || ! is_a($modelClass, Model::class, true)) {
                return null;
            }

            return $this->getTypeMaker()->makeFor(new $modelClass);
            // @codeCoverageIgnoreEnd
        }

        return $this->getTypeMaker()->makeFor(
            $this->getRelatedModelForRelation(
                $this->includeRelation($include)
            )
        );
    }

    /**
     * Returns whether a given include belongs to a singular relationship.
     *
     * @param string $include
     * @return bool
     */
    public function isRelationshipSingular($include)
    {
        return $this->isSingularRelation(
            $this->includeRelation($include)
        );
    }

    /**
     * Returns whether a given include belongs to a relationship with variable content.
     *
     * @param string $include
     * @return bool
     */
    public function isRelationshipVariable($include)
    {
        return $this->isVariableRelation(
            $this->includeRelation($include)
        );
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
     * @return Relation|Builder
     */
    protected function getModelRelation($method)
    {
        $model          = $this->getModel();
        $relationMethod = Str::camel($method);

        if ( ! method_exists($model, $relationMethod)) {
            throw new RuntimeException(
                "No method '{$relationMethod}' exists on model " . get_class($model) . " for relation '{$method}"
            );
        }

        $relation = $model->{$relationMethod}();

        if ( ! ($relation instanceof Relations\Relation)) {
            throw new RuntimeException(
                "Method '{$relationMethod}' on model " . get_class($model) . " is not a relation method"
            );
        }

        return $relation;
    }

    /**
     * Returns the model instance for a relation.
     *
     * @param Relation $relation
     * @return Model|null
     */
    protected function getRelatedModelForRelation(Relation $relation)
    {
        if ($relation instanceof Relations\MorphTo) {

            $modelClass = $this->model->{$relation->getMorphType()};

            if ($modelClass && is_a($modelClass, Model::class, true)) {
                return new $modelClass;
            }

            return null;
        }

        return $relation->getRelated();
    }

    /**
     * Returns whether given relation is singular.
     *
     * @param Relation $relation
     * @return bool
     */
    protected function isSingularRelation(Relation $relation)
    {
        return  $relation instanceof Relations\BelongsTo
            ||  $relation instanceof Relations\HasOne
            ||  $relation instanceof Relations\MorphOne
            ||  $relation instanceof Relations\MorphTo;
    }

    /**
     * Returns whether given relation may return varying model types.
     *
     * @param Relation $relation
     * @return bool
     */
    protected function isVariableRelation(Relation $relation)
    {
        return $relation instanceof Relations\MorphTo;
    }

    /**
     * @return string
     */
    protected function getModelClass()
    {
        return get_class($this->model);
    }

    /**
     * @return TypeMakerInterface
     */
    protected function getTypeMaker()
    {
        return app(TypeMakerInterface::class);
    }

    /**
     * @return ResourceRepositoryInterface
     */
    protected function getResourceRepository()
    {
        return app(ResourceRepositoryInterface::class);
    }
}
