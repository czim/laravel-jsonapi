<?php
namespace Czim\JsonApi\Facades;

use Czim\JsonApi\Contracts\Encoder\EncoderInterface;
use Illuminate\Support\Facades\Facade;

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
