<?php
namespace Czim\JsonApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Czim\JsonApi\Http\Requests\JsonApiRequest;

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
