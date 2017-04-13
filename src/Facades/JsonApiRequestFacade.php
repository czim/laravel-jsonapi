<?php
namespace Czim\JsonApi\Facades;

use Czim\JsonApi\Contracts\Support\Request\RequestQueryParserInterface;
use Illuminate\Support\Facades\Facade;

/**
 * Class JsonApiRequestFacade
 *
 * @see RequestQueryParserInterface
 * @see \Czim\JsonApi\Support\Request\RequestQueryParser
 */
class JsonApiRequestFacade extends Facade
{

    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return RequestQueryParserInterface::class;
    }

}
