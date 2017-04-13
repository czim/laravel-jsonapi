<?php
namespace Czim\JsonApi\Contracts\Support\Validation;

use Illuminate\Contracts\Support\MessageBag;

interface JsonApiValidatorInterface
{

    /**
     * Returns whether given array data validates against the basic JSON-API schema.
     *
     * @param array|object $data  data to be validated
     * @param string       $type  the type of schema to validate against
     * @return bool
     */
    public function validateSchema($data, $type = 'request');

    /**
     * Returns the errors detected in the last validate call.
     *
     * @return MessageBag
     */
    public function getErrors();

}
