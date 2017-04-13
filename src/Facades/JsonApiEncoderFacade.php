<?php
namespace Czim\JsonApi\Facades;

use Illuminate\Support\Facades\Facade;
use Czim\JsonApi\Contracts\Encoder\EncoderInterface;

/**
 * Class JsonApiEncoderFacade
 *
 * @see \Czim\JsonApi\Encoder\Encoder
 * @see \Czim\JsonApi\Contracts\Encoder\EncoderInterface
 */
class JsonApiEncoderFacade extends Facade
{

    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return EncoderInterface::class;
    }

}
