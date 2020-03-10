<?php
namespace Czim\JsonApi\Exceptions;

use Exception;

/**
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
     * @return $this|JsonApiValidationException
     */
    public function setErrors(array $errors): JsonApiValidationException
    {
        $this->errors = $errors;

        return $this;
    }

    /**
     * @param string $prefix
     * @return $this|JsonApiValidationException
     */
    public function setPrefix($prefix): JsonApiValidationException
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * @return array[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
