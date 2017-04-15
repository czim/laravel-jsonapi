<?php
namespace Czim\JsonApi\Test\Helpers\Controllers;

use Czim\JsonApi\Http\Requests\JsonApiCreateRequest;
use Czim\JsonApi\Http\Requests\JsonApiRequest;
use Illuminate\Routing\Controller;

class RequestTestController extends Controller
{

    /**
     * @param JsonApiRequest $request
     * @return mixed
     */
    public function request(JsonApiRequest $request)
    {
        return response([
            'data-root-type'    => $request->data()->getRootType(),
            'data'              => $request->data()->toArray(),
            'query-page-number' => $request->jsonApiQuery()->getPageNumber(),
        ]);
    }

    /**
     * @param JsonApiCreateRequest $request
     * @return mixed
     */
    public function create(JsonApiCreateRequest $request)
    {
        return response([
            'data-root-type'    => $request->data()->getRootType(),
            'data'              => $request->data()->toArray(),
        ]);
    }

}
