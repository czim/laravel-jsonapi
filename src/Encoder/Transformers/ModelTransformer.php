<?php
namespace Czim\JsonApi\Encoder\Transformers;

use Czim\JsonApi\Contracts\Resource\EloquentResourceInterface;
use Czim\JsonApi\Contracts\Resource\ResourceInterface;
use Czim\JsonApi\Enums\Key;
use Czim\JsonApi\Exceptions\EncodingException;
use Czim\JsonApi\Support\Resource\RelationshipTransformData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ModelTransformer extends AbstractTransformer
{
    /**
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
     * Returns serialized meta section for a given resource.
     *
     * @param ResourceInterface $resource
     * @return array
     */
    protected function serializeMetaData(ResourceInterface $resource)
    {
        return $resource->getMeta() ?: [];
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
     * @throws EncodingException
     */
    protected function processRelationships(ResourceInterface $resource)
    {
        $data = [];

        $defaultIncludes = $this->getDefaultIncludesIndex($resource);

        foreach ($resource->availableIncludes() as $key) {

            $fullyIncluded = $this->shouldIncludeFully($resource, $key, $defaultIncludes);

            $transformParameters = new RelationshipTransformData([
                'resource'   => $resource,
                'include'    => $key,
                'sideload'   => $fullyIncluded,
                'references' => $fullyIncluded || $resource->includeReferencesForRelation($key),
            ]);

            $transformer = $this->encoder->makeTransformer($transformParameters);
            $transformer->setParent($this->parent);

            $data[ $key ] = $transformer->transform($transformParameters);
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
     * Normalizes a model attribute key to a JSON-API attribute key.
     *
     * @param string $key
     * @return string
     */
    protected function normalizeJsonApiAttributeKey($key)
    {
        return str_replace('_', '-', Str::snake($key, '-'));
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
