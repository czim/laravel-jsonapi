<?php
namespace Czim\JsonApi\Exceptions;

/**
 * Exception container for 400 validation exceptions that occur when
 * the request querystring is malformed.
 */
class JsonApiQueryStringValidationException extends JsonApiValidationException
{
    /**
     * @var int
     */
    protected $statusCode = 400;
}
