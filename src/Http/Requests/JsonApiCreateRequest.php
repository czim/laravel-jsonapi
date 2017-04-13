<?php
namespace Czim\JsonApi\Http\Requests;

class JsonApiCreateRequest extends JsonApiRequest
{

    /**
     * The type of schema validation to apply.
     *
     * @var string
     */
    protected $schemaValidationType = 'create';

}
