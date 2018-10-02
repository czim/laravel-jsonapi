<?php
namespace Czim\JsonApi\Encoder;

use Czim\JsonApi\Enums\Key;
use Czim\JsonApi\Contracts\Encoder\EncoderInterface;
use Czim\JsonApi\Contracts\Encoder\TransformerFactoryInterface;
use Czim\JsonApi\Contracts\Encoder\TransformerInterface;
use Czim\JsonApi\Contracts\Repositories\ResourceRepositoryInterface;
use Czim\JsonApi\Contracts\Resource\ResourceInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Encoder implements EncoderInterface
{

    /**
     * Sideloaded included data.
     *
     * @var Collection
     */
    protected $included;

    /**
     * Top level links.
     *
     * @var Collection
     */
    protected $links;

    /**
     * Top level meta object.
     *
     * @var array
     */
    protected $meta;

    /**
     * The includes that were marked as requested by the client.
     *
     * @var string[]
     */
    protected $requestedIncludes = [];

    /**
     * @var TransformerFactoryInterface
     */
    protected $transformerFactory;

    /**
     * @var ResourceRepositoryInterface
     */
    protected $resourceRepository;

    /**
     * The base URL for the the top resource.
     *
     * This is used for building links relative to the top level,
     * such as for paginated results.
     *
     * @var string|null
     */
    protected $topResourceUrl;

    /**
     * Whether the set top resource URL is absolute.
     *
     * @var bool
     */
    protected $topResourceAbsolute = false;


    /**
     * @param TransformerFactoryInterface $transformerFactory
     * @param ResourceRepositoryInterface $resourceRepository
     */
    public function __construct(
        TransformerFactoryInterface $transformerFactory,
        ResourceRepositoryInterface $resourceRepository
    ) {
        $this->transformerFactory = $transformerFactory;
        $this->resourceRepository = $resourceRepository;

        $this->included = new Collection;
        $this->links    = new Collection;
    }


    /**
     * Encodes given data as JSON-API encoded data array.
     *
     * @param mixed      $data
     * @param array|null $includes
     * @return array
     */
    public function encode($data, array $includes = null)
    {
        if (null !== $includes) {
            $this->setRequestedIncludes($includes);
        }

        $this->beforeEncode();

        // First, perform the transformation, which should also update the encoder
        // with included data, links, meta data, etc.
        $encoded = $this->transform($data);

        // Append meta data
        if ($this->getMeta()) {
            $encoded[ Key::META ] = $this->getMeta();
        }

        // Serialize collected data and decorate the encoded data with it.
        if ($this->hasLinks()) {
            $encoded[ Key::LINKS ] = $this->serializeLinks();
        }
        
        // Make sure top resource is not in the included data
        if (array_key_exists(Key::DATA, $encoded)) {
            $id   = array_get($encoded[ Key::DATA ], 'id');
            $type = array_get($encoded[ Key::DATA ], 'type');

            if (null !== $type && null !== $id) {
                $this->removeFromIncludedDataByTypeAndId($type, $id);
            }
        }

        if ($this->hasIncludedData()) {
            $encoded[ Key::INCLUDED ] = $this->serializeIncludedData();
        }

        $this->afterEncode();

        return $encoded;
    }

    /**
     * Prepares the encoder for the next encode.
     */
    protected function beforeEncode()
    {
        if (null === $this->topResourceUrl && config('jsonapi.transform.auto-determine-top-resource-url')) {
            $this->topResourceUrl      = url()->current();
            $this->topResourceAbsolute = true;
        }
    }

    /**
     * Resets encoder state, ready for next encode.
     */
    protected function afterEncode()
    {
        $this->topResourceUrl      = null;
        $this->topResourceAbsolute = false;
    }

    /**
     * Returns transformer for given data in this context.
     *
     * @param mixed $data
     * @param bool  $topLevel
     * @return TransformerInterface
     */
    public function makeTransformer($data, $topLevel = false)
    {
        $transformer = $this->transformerFactory->makeFor($data);

        $transformer->setEncoder($this);

        if ($topLevel) {
            $transformer->setIsTop();
        }

        return $transformer;
    }

    /**
     * Transforms data for top level data key.
     *
     * Transformers may recursively prepare further nested data,
     * and may add included data on this encoder to be side-loaded.
     *
     * @param mixed $data
     * @return mixed
     */
    protected function transform($data)
    {
        $transformer = $this->makeTransformer($data, true);

        return $transformer->transform($data);
    }

    // ------------------------------------------------------------------------------
    //      Links and meta
    // ------------------------------------------------------------------------------

    /**
     * Returns the base URI to use for the API.
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return rtrim(config('jsonapi.base_url'), '/');
    }

    /**
     * Returns the base URI for the top resource, if any is set.
     *
     * @return null|string
     */
    public function getTopResourceUrl()
    {
        if ( ! is_string($this->topResourceUrl) || $this->topResourceAbsolute) {
            return $this->topResourceUrl;
        }

        return $this->getBaseUrl() . '/' . ltrim($this->topResourceUrl, '/');
    }

    /**
     * Sets the base top resource URI.
     *
     * This will be reset after encoding.
     *
     * @param string $url
     * @param bool   $absolute
     * @return $this
     */
    public function setTopResourceUrl($url, $absolute = false)
    {
        $this->topResourceUrl      = $url;
        $this->topResourceAbsolute = (bool) $absolute;

        return $this;
    }

    /**
     * Sets a top-level link.
     *
     * @param string       $key
     * @param string|array $link    may be string or [ href, meta ] array
     * @return $this
     */
    public function setLink($key, $link)
    {
        $this->links->put($key, $link);

        return $this;
    }

    /**
     * Removes a top level link.
     *
     * @param string $key
     * @return $this
     */
    public function removeLink($key)
    {
        $this->links->forget($key);

        return $this;
    }
    
    /**
     * Returns whether any meta data has been set.
     *
     * @return bool
     */
    public function hasMeta()
    {
        return (bool) count($this->getMeta());
    }

    /**
     * Returns currently set top-level meta section content.
     *
     * @return array
     */
    public function getMeta()
    {
        return $this->meta ?: [];
    }

    /**
     * Overwrites the meta section with data.
     *
     * @param array $data
     * @return $this
     */
    public function setMeta(array $data)
    {
        $this->meta = $data;

        return $this;
    }

    /**
     * Sets a top-level meta section value for a key.
     *
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    public function addMeta($key, $value)
    {
        if ( ! is_array($this->meta)) {
            $this->meta = [];
        }

        $this->meta = array_set($this->meta, $key, $value);

        return $this;
    }

    /**
     * Removes a top-level meta section value by key.
     *
     * @param string $key
     * @return $this
     */
    public function removeMetaKey($key)
    {
        if ( ! is_array($this->meta)) {
            return $this;
        }

        array_forget($this->meta, $key);

        return $this;
    }


    // ------------------------------------------------------------------------------
    //      Includes, requested
    // ------------------------------------------------------------------------------

    /**
     * Sets requested includes for transformation.
     *
     * @param array $includes
     * @return $this
     */
    public function setRequestedIncludes(array $includes)
    {
        $this->requestedIncludes = $includes;

        return $this;
    }

    /**
     * Returns currently registered requested includes.
     *
     * @return string[]
     */
    public function getRequestedIncludes()
    {
        return $this->requestedIncludes;
    }

    /**
     * Returns whether any includes are requested.
     *
     * @return bool
     */
    public function hasRequestedIncludes()
    {
        return (bool) count($this->requestedIncludes);
    }

    /**
     * Returns whether a dot-notated include is requested.
     *
     * This WILL report some.relation to be requested when some.relation.deeper is requested.
     *
     * @param string $key
     * @return bool
     */
    public function isIncludeRequested($key)
    {
        foreach ($this->requestedIncludes as $include) {
            if ($include === $key || starts_with($include, $key . '.')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns whether any links were collected.
     *
     * @return bool
     */
    protected function hasLinks()
    {
        return $this->links->isNotEmpty();
    }

    /**
     * Returns collected top level links as array.
     *
     * @return array
     */
    protected function serializeLinks()
    {
        $links = $this->links->toArray();

        ksort($links);

        return $links;
    }


    // ------------------------------------------------------------------------------
    //      Included data, side-loading
    // ------------------------------------------------------------------------------

    /**
     * Adds data to be included by side-loading.
     *
     * @param mixed       $data
     * @param string|null $identifier    uniquely identifies the included data, if possible
     * @return $this
     */
    public function addIncludedData($data, $identifier = null)
    {
        if (null === $identifier) {
            $this->included->push($data);
        } elseif ( ! $this->included->has($identifier)) {
            $this->included->put($identifier, $data);
        }

        return $this;
    }

    /**
     * Removes included data by identifier.
     *
     * @param string $identifier
     * @return $this
     */
    public function removeIncludedData($identifier)
    {
        $this->included->forget($identifier);

        return $this;
    }

    /**
     * Removes included data by a given type and id.
     *
     * @param string $type
     * @param string $id
     * @return $this
     */
    public function removeFromIncludedDataByTypeAndId($type, $id)
    {
        return $this->removeIncludedData($type . ':' . $id);
    }

    /**
     * Returns whether any data to be included was collected.
     *
     * @return bool
     */
    protected function hasIncludedData()
    {
        return $this->included->isNotEmpty();
    }

    /**
     * Returns collected included data as array.
     *
     * @return array
     */
    protected function serializeIncludedData()
    {
        return $this->included->values()->toArray();
    }


    // ------------------------------------------------------------------------------
    //      Resource provision
    // ------------------------------------------------------------------------------

    /**
     * Returns resource for given model instance.
     *
     * @param Model $model
     * @return null|ResourceInterface
     */
    public function getResourceForModel(Model $model)
    {
        $resource = $this->resourceRepository->getByModel($model);

        if ($resource) {
            $resource->setModel($model);
        }

        return $resource;
    }

    /**
     * Returns resource for given JSON-API resource type.
     *
     * @param string $type
     * @return null|ResourceInterface
     */
    public function getResourceForType($type)
    {
        return $this->resourceRepository->getByType($type);
    }

}
