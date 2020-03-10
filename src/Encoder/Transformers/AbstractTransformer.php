<?php
namespace Czim\JsonApi\Encoder\Transformers;

use Czim\JsonApi\Contracts\Encoder\EncoderInterface;
use Czim\JsonApi\Contracts\Encoder\TransformerInterface;

abstract class AbstractTransformer implements TransformerInterface
{
    /**
     * Parent encoder instance.
     *
     * @var EncoderInterface
     */
    protected $encoder;

    /**
     * Whether to transform as top-level resource.
     *
     * @var bool
     */
    protected $isTop = false;

    /**
     * Parent dot-notation key chain.
     *
     * This should correspond to the dot-notation of the includes.
     *
     * @var string
     */
    protected $parent;

    /**
     * Whether the results in a collection are of variable model type.
     *
     * @var bool
     */
    protected $isVariable = false;


    /**
     * Sets parent encoder instance.
     *
     * @param EncoderInterface $encoder
     * @return $this
     */
    public function setEncoder(EncoderInterface $encoder): TransformerInterface
    {
        $this->encoder = $encoder;

        return $this;
    }

    /**
     * Sets that the transformation is for a top-level resource.
     *
     * @param bool $top
     * @return $this
     */
    public function setIsTop(bool $top = true): TransformerInterface
    {
        $this->isTop = (bool) $top;

        return $this;
    }

    /**
     * Sets the dot-notation parent chain.
     *
     * @param string|null $parentChain
     * @return $this
     */
    public function setParent(?string $parentChain): TransformerInterface
    {
        $this->parent = $parentChain;

        return $this;
    }

    /**
     * Sets whether the collection may contain more than one type of model.
     *
     * @param bool $variable
     * @return $this
     */
    public function setIsVariable(bool $variable = true): TransformerInterface
    {
        $this->isVariable = (bool) $variable;

        return $this;
    }
}
