<?php
namespace Czim\JsonApi\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Czim\JsonApi\Encoding\JsonApiEncoder
 */
class Encoder extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'jsonapi.encoder';
    }

}
