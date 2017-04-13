<?php
namespace Czim\JsonApi\Facades;

use Illuminate\Support\Facades\Facade;
use Czim\JsonApi\Contracts\Support\Request\RequestQueryParserInterface;

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
