<?php
namespace Czim\JsonApi\Encoder\Transformers;

use Czim\JsonApi\Contracts\Resource\EloquentResourceInterface;
use Czim\JsonApi\Contracts\Resource\ResourceInterface;
use Czim\JsonApi\Enums\Key;
use Czim\JsonApi\Exceptions\EncodingException;
use Czim\JsonApi\Support\Resource\RelationshipTransformData;
use InvalidArgumentException;

class ModelRelationshipTransformer extends AbstractTransformer
{

    /**
     * Transforms given data.
     *
     * @param RelationshipTransformData $parameters
     * @return array
     * @throws EncodingException
     */
    public function transform($parameters)
    {
        if ( ! ($parameters instanceof RelationshipTransformData)) {
            throw new InvalidArgumentException('ModelRelationshipTransformer expects RelationshipTransformData instance');
        }

        /** @var EloquentResourceInterface $resource */
        $resource = $parameters->resource;
        $include  = $parameters->include;

        if ( ! ($resource instanceof EloquentResourceInterface)) {
            throw new EncodingException("ModelRelationshipTransformer expects data with EloquentResourceInterface resource");
        }

        if ( ! $include || ! in_array($include, $resource->availableIncludes())) {
            throw new EncodingException(
                'ModelRelationshipTransformer expects data with a valid include for resource'
                . " '" . get_class($resource) . "'"
            );
        }


        $data = [
            Key::LINKS => $this->getLinksData($resource, $include)
        ];

        if ( ! count($data[ Key::LINKS ])) {
            unset($data[ Key::LINKS ]);
        }

        // Set data and side-load includes

        // References (type/id) should be added as data for the relationship if:
        // a. a relationship is included by default or by the client
        // b. a relationship is marked to always have references included in the resource
        if ($parameters->sideload || $parameters->references) {

            // Get nested data, either plucking the keys for the related model,
            // or simply retrieving the entire model/collection.

            if ($parameters->sideload) {

                // If to be fully included for sideloading, also add the information to the encoder.
                // This data must be transformed using a relevant transformer.

                // If fully included, get the type/id references from the transformed data
                // to prevent redundant processing.

                $singular = $resource->isRelationshipSingular($include);

                $related = $this->getRelatedFullData($resource, $include);

                $this->addRelatedDataToEncoder($related, $singular);

                if (empty(array_get($related, Key::DATA))) {
                    $data[ Key::DATA ] = array_get($related, Key::DATA);
                } else {
                    $data[ Key::DATA ] = $this->getRelatedReferencesFromRelatedData($related, $singular);
                }

            } else {

                $data[ Key::DATA ] = $this->getRelatedReferenceData($resource, $include);
            }
        }

        return $data;
    }

    /**
     * Returns JSON-API links section data.
     *
     * @param ResourceInterface $resource
     * @param string            $key
     * @return array
     */
    protected function getLinksData(ResourceInterface $resource, $key)
    {
        $data = [];

        if ($this->addRelationshipsLink()) {
            $data[ Key::LINK_SELF ] = rtrim($resource->url(), '/') . '/'
                . $resource->id() . '/'
                . $this->getRelationshipsLinkSegment()
                . $key;
        }

        if ($this->addRelatedLink()) {
            $data[ KEY::LINK_RELATED ] = rtrim($resource->url(), '/') . '/'
                . $resource->id() . '/'
                . $this->getRelatedLinkSegment()
                . $key;
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
        $transformer->setParent(trim($this->parent . '.' . $includeKey, '.'));
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
     * @return bool
     */
    protected function addRelationshipsLink()
    {
        return (bool) config('jsonapi.transform.links.relationships');
    }

    /**
     * @return bool
     */
    protected function addRelatedLink()
    {
        return (bool) config('jsonapi.transform.links.related');
    }

    /**
     * @return string
     */
    protected function getRelationshipsLinkSegment()
    {
        $segment = config('jsonapi.transform.links.relationships-segment', 'relationships');

        if ( ! $segment) {
            return '';
        }

        return rtrim($segment, '/') . '/';
    }

    /**
     * @return string
     */
    protected function getRelatedLinkSegment()
    {
        $segment = config('jsonapi.transform.links.related-segment', 'related');

        if ( ! $segment) {
            return '';
        }

        return rtrim($segment, '/') . '/';
    }

}
