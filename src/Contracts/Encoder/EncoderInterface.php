<?php
namespace Czim\JsonApi\Contracts\Encoder;

use Czim\JsonApi\Contracts\Resource\ResourceInterface;
use Illuminate\Database\Eloquent\Model;

interface EncoderInterface
{
    /**
     * Encodes given data as JSON-API encoded data array.
     *
     * @param mixed      $data
     * @param array|null $includes      requested includes, if not null
     * @return array
     */
    public function encode($data, array $includes = null): array;

    /**
     * Returns transformer for given data in this context.
     *
     * @param mixed $data
     * @param bool  $topLevel
     * @return TransformerInterface
     */
    public function makeTransformer($data, bool $topLevel = false): TransformerInterface;


    /**
     * Returns the base URI to use for the API.
     *
     * @return string
     */
    public function getBaseUrl(): string;

    /**
     * Returns the base URI for the top resource, if any is set.
     *
     * @return null|string
     */
    public function getTopResourceUrl(): ?string;

    /**
     * Sets the base top resource URI.
     *
     * This will be reset after encoding.
     *
     * @param string $url
     * @param bool   $absolute
     * @return $this|EncoderInterface
     */
    public function setTopResourceUrl(string $url, bool $absolute = false): EncoderInterface;

    /**
     * Sets a top-level link.
     *
     * @param string       $key
     * @param string|array $link
     * @return $this|EncoderInterface
     */
    public function setLink(string $key, $link): EncoderInterface;

    /**
     * Removes a top level link.
     *
     * @param string $key
     * @return $this|EncoderInterface
     */
    public function removeLink(string $key): EncoderInterface;

    /**
     * Returns currently set top-level meta section content.
     *
     * @return array
     */
    public function getMeta();

    /**
     * Overwrites the meta section with data.
     *
     * @param array $data
     * @return $this|EncoderInterface
     */
    public function setMeta(array $data): EncoderInterface;

    /**
     * Sets a top-level meta section value for a key.
     *
     * @param string $key
     * @param mixed  $value
     * @return $this|EncoderInterface
     */
    public function addMeta(string $key, $value): EncoderInterface;

    /**
     * Removes a top-level meta section value by key.
     *
     * @param string $key
     * @return $this|EncoderInterface
     */
    public function removeMetaKey(string $key): EncoderInterface;


    /**
     * Sets requested includes for transformation.
     *
     * @param array $includes
     * @return $this|EncoderInterface
     */
    public function setRequestedIncludes(array $includes): EncoderInterface;

    /**
     * Returns currently registered requested includes.
     *
     * @return string[]
     */
    public function getRequestedIncludes(): array;

    /**
     * Returns whether any includes are requested.
     *
     * @return bool
     */
    public function hasRequestedIncludes(): bool;

    /**
     * Returns whether a dot-notated include is requested.
     *
     * This WILL report some.relation to be requested when some.relation.deeper is requested.
     *
     * @param string $key
     * @return bool
     */
    public function isIncludeRequested(string $key): bool;

    /**
     * Adds data to be included by side-loading.
     *
     * @param mixed       $data
     * @param string|null $identifier    uniquely identifies the included data, if possible
     * @return $this|EncoderInterface
     */
    public function addIncludedData($data, ?string $identifier = null): EncoderInterface;


    /**
     * Returns resource for given model instance.
     *
     * @param Model $model
     * @return null|ResourceInterface
     */
    public function getResourceForModel(Model $model): ?ResourceInterface;

    /**
     * Returns resource for given JSON-API resource type.
     *
     * @param string $type
     * @return null|ResourceInterface
     */
    public function getResourceForType(string $type): ?ResourceInterface;
}
