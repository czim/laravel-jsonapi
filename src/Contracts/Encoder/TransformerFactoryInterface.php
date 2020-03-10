<?php
namespace Czim\JsonApi\Contracts\Encoder;

interface TransformerFactoryInterface
{
    /**
     * Makes a transformer for given data.
     *
     * @param mixed $data
     * @return TransformerInterface
     */
    public function makeFor($data);

}
