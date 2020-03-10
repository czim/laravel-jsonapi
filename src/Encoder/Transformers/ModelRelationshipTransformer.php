<?php
namespace Czim\JsonApi\Encoder\Transformers;

use Czim\JsonApi\Contracts\Resource\EloquentResourceInterface;
use Czim\JsonApi\Contracts\Resource\ResourceInterface;
use Czim\JsonApi\Enums\Key;
use Czim\JsonApi\Exceptions\EncodingException;
use Czim\JsonApi\Support\Resource\RelationshipTransformData;
use Illuminate\Support\Arr;
use InvalidArgumentException;

class ModelRelationshipTransformer extends AbstractTransformer
{
    /**
     * @param mixed|RelationshipTransformData $parameters
     * @return array
     * @throws EncodingException
     */
    public function transform($parameters): array
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

                if (empty(Arr::get($related, Key::DATA))) {
                    $data[ Key::DATA ] = Arr::get($related, Key::DATA);
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
    protected function getLinksData(ResourceInterface $resource, string $key): array
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
     * @throws EncodingException
     */
    protected function getRelatedFullData(ResourceInterface $resource, string $includeKey): array
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
     * @return array|null
     */
    protected function getRelatedReferenceData(ResourceInterface $resource, string $includeKey): ?array
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
    protected function getRelatedReferencesFromRelatedData(array $data, bool $singular = false): array
    {
        $data = Arr::get($data, Key::DATA, []);

        if ($singular) {
            return [
                'type' => Arr::get($data, 'type'),
                'id'   => Arr::get($data, 'id'),
            ];
        }

        return array_map(
            function ($related) {
                return [
                    'type' => Arr::get($related, 'type'),
                    'id'   => Arr::get($related, 'id'),
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
    protected function addRelatedDataToEncoder(?array $data, bool $singular = true): void
    {
        if ( ! is_array($data)) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        if ($singular) {
            $data = [ Arr::get($data, Key::DATA) ];
        } else {
            $data = Arr::get($data, Key::DATA, []);
        }

        foreach ($data as $related) {
            if ( ! is_array($related)) {
                continue;
            }

            $identifier = Arr::get($related, 'type') . ':' . Arr::get($related, 'id');
            $this->encoder->addIncludedData($related, $identifier);
        }
    }

    protected function addRelationshipsLink(): bool
    {
        return (bool) config('jsonapi.transform.links.relationships');
    }

    protected function addRelatedLink(): bool
    {
        return (bool) config('jsonapi.transform.links.related');
    }

    protected function getRelationshipsLinkSegment(): string
    {
        $segment = config('jsonapi.transform.links.relationships-segment', 'relationships');

        if ( ! $segment) {
            return '';
        }

        return rtrim($segment, '/') . '/';
    }

    protected function getRelatedLinkSegment(): string
    {
        $segment = config('jsonapi.transform.links.related-segment', 'related');

        if ( ! $segment) {
            return '';
        }

        return rtrim($segment, '/') . '/';
    }
}
