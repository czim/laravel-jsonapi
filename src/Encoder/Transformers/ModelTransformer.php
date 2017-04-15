<?php
namespace Czim\JsonApi\Encoder\Transformers;

use Czim\JsonApi\Contracts\Resource\ResourceInterface;
use Czim\JsonApi\Enums\Key;
use Czim\JsonApi\Exceptions\EncodingException;
use Czim\JsonApi\Support\Resource\RelationData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Database\Eloquent\Relations\Relation;
use InvalidArgumentException;
use RuntimeException;
use UnexpectedValueException;

class ModelTransformer extends AbstractTransformer
{

    /**
     * Transforms given data.
     *
     * @param Model $model
     * @return array
     * @throws EncodingException
     */
    public function transform($model)
    {
        if ( ! ($model instanceof Model)) {
            throw new InvalidArgumentException('ModelTransformer expects Eloquent model instance');
        }

        if ( ! ($resource = $this->getResourceForModel($model))) {
            throw new EncodingException("Could not determine resource for '" . get_class($model) . "'");
        }

        $resource->setModel($model);

        $data = [
            'id'               => $resource->id(),
            'type'             => $resource->type(),
            Key::ATTRIBUTES    => $this->serializeAttributes($resource),
            Key::RELATIONSHIPS => $this->processRelationships($resource),
            Key::META          => $this->serializeMetaData($resource),
        ];

        if ( ! count($data[ Key::ATTRIBUTES ])) {
            unset($data[ Key::ATTRIBUTES ]);
        }

        if ( ! count($data[ Key::RELATIONSHIPS ])) {
            unset($data[ Key::RELATIONSHIPS ]);
        }

        if ( ! count($data[ Key::META ])) {
            unset($data[ Key::META ]);
        }

        return [
            Key::DATA => $data,
        ];
    }

    /**
     * Returns resource for given model instance.
     *
     * @param Model $model
     * @return null|ResourceInterface
     */
    protected function getResourceForModel(Model $model)
    {
        return $this->encoder->getResourceForModel($model);
    }

    /**
     * Returns base URI for the resource.
     *
     * @param ResourceInterface $resource
     * @return string
     */
    protected function getBaseResourceUrl(ResourceInterface $resource)
    {
        return $this->encoder->getBaseUrl() . '/' . $resource->type();
    }

    /**
     * Returns serialized meta section for a given resource.
     *
     * @param ResourceInterface $resource
     * @return array|null
     */
    protected function serializeMetaData(ResourceInterface $resource)
    {
        return $resource->getMeta();
    }

    /**
     * Returns serialized attributes for a given resource.
     *
     * @param ResourceInterface $resource
     * @return array
     */
    protected function serializeAttributes(ResourceInterface $resource)
    {
        $data = [];

        foreach ($resource->availableAttributes() as $key) {
            $data[ $this->normalizeJsonApiAttributeKey($key) ] = $resource->attributeValue($key);
        }

        return $data;
    }

    /**
     * Processes and serializes relationships for a given resource.
     *
     * @param ResourceInterface $resource
     * @return array
     */
    protected function processRelationships(ResourceInterface $resource)
    {
        $data = [];

        $defaultIncludes = $this->getDefaultIncludesIndex($resource);

        foreach ($resource->availableIncludes() as $key) {

            // Analyze the relationship, determine the JSON-API type
            $relationData = $this->getRelationData($resource, $key);
            $relatedType  = null;

            if ($relationData->model) {

                $relatedResource = $this->encoder->getResourceForModel($relationData->model);

                if ($relatedResource) {
                    $relatedResource->setModel($relationData->model);
                    $relatedType = $relatedResource->type();
                }
            }


            $data[ $key ] = [
                Key::LINKS => $this->getLinksData($resource, $key, $relatedType)
            ];


            // Set data and side-load includes

            $fullyIncluded = $this->shouldIncludeFully($resource, $key, $defaultIncludes);

            // References (type/id) should be added as data for the relationship if:
            // a. a relationship is included by default or by the client
            // b. a relationship is marked to always have references included in the resource
            if ($fullyIncluded || $resource->includeReferencesForRelation($key)) {

                // Get nested data, either plucking the keys for the related model,
                // or simply retrieving the entire model/collection.

                if ($fullyIncluded) {

                    // If fully included, also add the information to the encoder.
                    // This data must be transformed using a relevant transformer.

                    // If fully included, get the type/id references from the transformed data
                    // to prevent redundant processing.

                    $related = $this->getRelatedFullData($resource, $relationData);
                    $this->addRelatedDataToEncoder($related, $relationData->singular);

                    if (empty(array_get($related, Key::DATA))) {
                        $data[ $key ][ Key::DATA ] = array_get($related, Key::DATA);
                    } else {
                        $data[ $key ][ Key::DATA ] = $this->getRelatedReferencesFromRelatedData(
                            $related,
                            $relationData->singular
                        );
                    }

                } else {

                    $data[ $key ][ Key::DATA ] = $this->getRelatedReferenceData($resource, $relationData);
                }
            }
        }

        return $data;
    }

    /**
     * Returns indexed list of default includes, with keys as the includes.
     *
     * This allows quick lookup by checking whether an include key exists.
     *
     * @param ResourceInterface $resource
     * @return array
     */
    protected function getDefaultIncludesIndex(ResourceInterface $resource)
    {
        return array_flip($resource->defaultIncludes());
    }

    /**
     * @param ResourceInterface $resource
     * @param string            $key
     * @param array|null        $defaults       associative index, keys should be include keys.
     * @return bool
     */
    protected function shouldIncludeFully(ResourceInterface $resource, $key, array $defaults = null)
    {
        // Only consider default includes if we're at top level or allowing nested defaults.
        // Also ignore the defaults if we have configured requested defaults to cancel them out,
        // and they are set.
        if (    ! $this->isTop && $this->shouldAllowTopLevelIncludesOnly()
            ||  $this->encoder->hasRequestedIncludes() && $this->shouldIgnoreDefaultIncludesWhenRequestedSet()
        ) {
            return $this->encoder->isIncludeRequested($key);
        }

        // Otherwise, check whether the include is requested or default

        if (null === $defaults) {
            $defaults = $this->getDefaultIncludesIndex($resource);
        }

        return $this->encoder->isIncludeRequested($key) || array_key_exists($key, $defaults);
    }

    /**
     * Returns JSON-API links section data.
     *
     * @param ResourceInterface $resource
     * @param string            $key
     * @param string            $relatedType
     * @return array
     */
    protected function getLinksData(ResourceInterface $resource, $key, $relatedType)
    {
        $data = [
            Key::LINK_SELF => $this->getBaseResourceUrl($resource) . '/'
                            . $resource->id() . '/'
                            . $this->getRelationshipsSegment() . '/'
                            . $key,
        ];

        // If the relation is not morph/variable, add the related link
        if ($relatedType) {
            $data[ KEY::LINK_RELATED ] = $this->getBaseResourceUrl($resource) . '/'
                                       . $resource->id() . '/'
                                       . $relatedType;
        }

        return $data;
    }

    /**
     * Returns transformed data for full includes
     *
     * @param ResourceInterface $resource
     * @param RelationData      $relation
     * @return array
     */
    protected function getRelatedFullData(ResourceInterface $resource, RelationData $relation)
    {
        $includeKey = $relation->key;
        $method     = $resource->getRelationMethodForInclude($includeKey);

        $related = $resource->getModel()->{$method};

        $transformer = $this->encoder->makeTransformer($related);
        $transformer->setParent($this->parent . '.' . $includeKey);

        // For nullable singular relations, make sure we return data normalized under a data key
        // The recursive transformer call cannot detect this, since it will only see a NULL value.
        if (null === $related) {
            return [ Key::DATA => null ];
        }

        return $transformer->transform($related);
    }

    /**
     * @param ResourceInterface $resource
     * @param RelationData      $relation
     * @return array
     */
    protected function getRelatedReferenceData(ResourceInterface $resource, RelationData $relation)
    {
        if ( ! $relation->model) {
            // Should be acceptable for morphTo, since it simply means that the FK is null
            if ($relation->variable) {
                return $relation->singular ? null : [];
            }

            throw new UnexpectedValueException("Could not determine related model for related reference data lookup");
        }

        $relatedResource = $this->encoder->getResourceForModel($relation->model);

        if ( ! $relatedResource) {
            throw new RuntimeException("Could not determine resource for model '" . get_class($relation->model) . "'");
        }

        $includeKey = $relation->key;
        $keyName    = $relation->model->getKeyName();
        $method     = $resource->getRelationMethodForInclude($includeKey);

        if ($resource->getModel()->relationLoaded($method)) {
            $ids = $resource->getModel()->{$method}->pluck($keyName)->toArray();
        } else {
            $ids = $resource->includeRelation($includeKey)->pluck($keyName)->toArray();
        }

        if ($relation->singular) {
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
     * Registers related data with encoder for full top level side-loaded includes.
     *
     * @param null|array|array[] $data
     * @param bool               $singular      whether the relation is singular
     */
    protected function addRelatedDataToEncoder($data, $singular = true)
    {
        if ( ! is_array($data)) {
            return;
        }

        if ($singular) {
            $data = [ array_get($data, Key::DATA) ];
        } else {
            $data = array_get($data, Key::DATA, []);
        }

        foreach ($data as $related) {
            if ( ! is_array($related)) {
                continue;
            }

            $identifier = array_get($related, 'type') . ':' . array_get($related, 'id');
            $this->encoder->addIncludedData($related, $identifier);
        }
    }

    /**
     * Extracts type/id references from full include data
     *
     * @param array $data
     * @param bool  $singular
     * @return array
     */
    protected function getRelatedReferencesFromRelatedData(array $data, $singular = false)
    {
        $data = array_get($data, Key::DATA, []);

        if ($singular) {
            return [
                'type' => array_get($data, 'type'),
                'id'   => array_get($data, 'id'),
            ];
        }

        return array_map(
            function ($related) {
                return [
                    'type' => array_get($related, 'type'),
                    'id'   => array_get($related, 'id'),
                ];
            },
            $data
        );
    }

    // ------------------------------------------------------------------------------
    //      Analyze Relations
    // ------------------------------------------------------------------------------

    /**
     * Makes relation data for relation key on resource.
     *
     * @param ResourceInterface $resource
     * @param string            $key
     * @return RelationData
     */
    protected function getRelationData(ResourceInterface $resource, $key)
    {
        $relation = $resource->includeRelation($key);
        $variable = $this->isVariableRelation($relation);
        $model    = $variable ? null : $relation->getRelated();

        if ($relation instanceof Relations\MorphTo) {
            $modelClass = $relation->getMorphType();
            if ($modelClass && is_a($modelClass, Model::class, true)) {
                $model = new $modelClass;
            }
        }

        return new RelationData([
            'key'      => $key,
            'variable' => $variable,
            'singular' => $this->isSingularRelation($relation),
            'relation' => $relation,
            'model'    => $model,
        ]);
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
     * Returns whether given relation is variable (morphed).
     *
     * @param Relation $relation
     * @return bool
     */
    protected function isVariableRelation(Relation $relation)
    {
        return $relation instanceof Relations\MorphTo;
    }

    /**
     * Normalizes a model attribute key to a JSON-API attribute key.
     *
     * @param string $key
     * @return string
     */
    protected function normalizeJsonApiAttributeKey($key)
    {
        return str_replace('_', '-', snake_case($key, '-'));
    }

    /**
     * @return bool
     */
    protected function shouldAllowTopLevelIncludesOnly()
    {
        return (bool) config('jsonapi.transform.top-level-default-includes-only');
    }

    /**
     * @return bool
     */
    protected function shouldIgnoreDefaultIncludesWhenRequestedSet()
    {
        return (bool) config('jsonapi.transform.requested-includes-cancel-defaults');
    }

    /**
     * @return string
     */
    protected function getRelationshipsSegment()
    {
        return config('jsonapi.transform.links.relationships-segment', 'relationships');
    }

}
