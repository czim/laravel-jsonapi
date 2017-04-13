<?php
namespace Czim\JsonApi\Http\Middleware;

use Closure;
use Czim\JsonApi\Http\Requests\JsonApiRequest;
use Illuminate\Http\Request;

class DecorateJsonApiRequest
{

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // todo: find out how to properly 'decorate' the request..

        /** @var JsonApiRequest $jsonApiRequest */
        $jsonApiRequest = app(JsonApiRequest::class);

        return $next($jsonApiRequest);
    }


}
