<?php
namespace Czim\JsonApi\Encoder\Transformers;

use Czim\JsonApi\Contracts\Resource\EloquentResourceInterface;
use Czim\JsonApi\Contracts\Resource\ResourceInterface;
use Czim\JsonApi\Enums\Key;
use Czim\JsonApi\Exceptions\EncodingException;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

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

        if ($resource instanceof EloquentResourceInterface) {
            $resource->setModel($model);
        }


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
        return $resource->url();
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

            $relatedType = $resource->relationshipType($key);

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

                    $singular = $resource->isRelationshipSingular($key);

                    $related = $this->getRelatedFullData($resource, $key);

                    $this->addRelatedDataToEncoder($related, $singular);

                    if (empty(array_get($related, Key::DATA))) {
                        $data[ $key ][ Key::DATA ] = array_get($related, Key::DATA);
                    } else {
                        $data[ $key ][ Key::DATA ] = $this->getRelatedReferencesFromRelatedData($related, $singular);
                    }

                } else {

                    $data[ $key ][ Key::DATA ] = $this->getRelatedReferenceData($resource, $key);
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
        // Always include relations specifically requested
        if ($this->encoder->isIncludeRequested($this->prefixParentToIncludeKey($key))) {
            return true;
        };

        // Only consider default includes if we're at top level or allowing nested defaults.
        // Also ignore the defaults if we have configured requested defaults to cancel them out,
        // and they are set.
        if (    ! $this->isTop && $this->shouldAllowTopLevelIncludesOnly()
            ||  $this->encoder->hasRequestedIncludes() && $this->shouldIgnoreDefaultIncludesWhenRequestedSet()
        ) {
            return false;
        }

        // Otherwise, allow default includes
        if (null === $defaults) {
            // @codeCoverageIgnoreStart
            $defaults = $this->getDefaultIncludesIndex($resource);
            // @codeCoverageIgnoreEnd
        }

        return array_key_exists($key, $defaults);
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
     * @param string            $includeKey
     * @return array
     */
    protected function getRelatedFullData(ResourceInterface $resource, $includeKey)
    {
        $related = $resource->relationshipData($includeKey);

        $transformer = $this->encoder->makeTransformer($related);
        $transformer->setParent($this->parent . '.' . $includeKey);
        $transformer->setIsVariable($resource->isRelationshipVariable($includeKey));

        // For nullable singular relations, make sure we return data normalized under a data key
        // The recursive transformer call cannot detect this, since it will only see a NULL value.
        if (null === $related) {
            return [ Key::DATA => null ];
        }

        return $transformer->transform($related);
    }

    /**
     * @param ResourceInterface $resource
     * @param string            $includeKey
     * @return array
     */
    protected function getRelatedReferenceData(ResourceInterface $resource, $includeKey)
    {
        return $resource->relationshipReferences($includeKey);
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
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
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

    /**
     * Prefixes the parent key chain to an include key.
     *
     * @param string $key
     * @return string
     */
    protected function prefixParentToIncludeKey($key)
    {
        if (null === $this->parent) {
            return $key;
        }

        return $this->parent . '.'  . $key;
    }

}
