<?php
namespace Czim\JsonApi\Http\Responses;

use Illuminate\Http\JsonResponse;

class JsonApiResponse extends JsonResponse
{

    /**
     * {@inheritdoc}
     */
    public function __construct($data = null, $status = 200, $headers = [], $options = 0)
    {
        parent::__construct($data, $status, $headers, $options);

        $this->header('content-type', 'application/vnd.api+json');
    }

}
