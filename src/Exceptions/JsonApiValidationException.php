<?php
namespace Czim\JsonApi\Exceptions;

use Exception;

/**
 * Class JsonApiValidationException
 *
 * Exception container for 422 validation exceptions so they can be
 * uniformly rendered as an array of JSON-API error objects.
 */
class JsonApiValidationException extends Exception
{

    /**
     * Validation errors by key.
     *
     * @var array[]
     */
    protected $errors = [];

    /**
     * The error key prefix.
     *
     * If the errors are all data -> attributes, set the prefix to 'data/attributes/'.
     * Each key will be prefixed to allow using it for JSON-API pointers.
     *
     * @var string|null
     */
    protected $prefix;

    /**
     * @var int
     */
    protected $statusCode = 422;

    /**
     * Sets the validation errors.
     *
     * @param array $errors
     * @return $this
     */
    public function setErrors(array $errors)
    {
        $this->errors = $errors;

        return $this;
    }

    /**
     * @param string $prefix
     * @return $this
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * @return array[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return null|string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

}
