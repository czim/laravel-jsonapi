<?php
namespace Czim\JsonApi\Contracts\Encoder;

use Czim\JsonApi\Exceptions\EncodingException;

interface TransformerInterface
{
    /**
     * Sets parent encoder instance.
     *
     * @param EncoderInterface $encoder
     * @return $this|TransformerInterface
     */
    public function setEncoder(EncoderInterface $encoder): TransformerInterface;

    /**
     * Sets that the transformation is for a top-level resource.
     *
     * @param bool $top
     * @return $this|TransformerInterface
     */
    public function setIsTop(bool $top = true): TransformerInterface;

    /**
     * Sets the dot-notation parent chain.
     *
     * @param string|null $parentChain
     * @return $this|TransformerInterface
     */
    public function setParent(?string $parentChain): TransformerInterface;

    /**
     * Sets whether the collection may contain more than one type of model.
     *
     * @param bool $variable
     * @return $this|TransformerInterface
     */
    public function setIsVariable(bool $variable = true): TransformerInterface;

    /**
     * Transforms given data.
     *
     * @param mixed $data
     * @return array
     * @throws EncodingException
     */
    public function transform($data): array;
}
