<?php
namespace Czim\JsonApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\AcceptHeader;

/**
 * Class JsonApiHeaders
 *
 * This throw the JSON-API prescribed 415 error if headers are incorrect.
 */
class RequireJsonApiHeader
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ( ! $this->acceptHeaderValid($request)) {
            return response('', 406);
        }

        if ( ! $this->contentTypeHeaderValid(($request))) {
            return response('', 415);
        }

        return $next($request);
    }

    /**
     * @param Request $request
     * @return bool
     */
    protected function acceptHeaderValid(Request $request)
    {
        $acceptHeader = AcceptHeader::fromString($request->header('accept'));

        if ($acceptHeader->has('application/vnd.api+json')) {
            return empty($acceptHeader->get('application/vnd.api+json')->getAttributes());
        }

        return true;
    }

    /**
     * @param Request $request
     * @return bool
     */
    protected function contentTypeHeaderValid(Request $request)
    {
        $contentTypeHeader = AcceptHeader::fromString($request->header('content-type'));

        // also allowed to be multipart formdata and exceptional standard json
        if (    $contentTypeHeader->has('multipart/form-data')
            ||  $contentTypeHeader->has('application/json')
        ) {
            return true;
        }

        if ($contentTypeHeader->has('application/vnd.api+json')) {
            $attributes = $contentTypeHeader->get('application/vnd.api+json')->getAttributes();

            return (    empty($attributes)
                    ||  (   count($attributes) === 1
                        &&  $contentTypeHeader->get('application/vnd.api+json')->getAttribute('charset') === 'UTF-8'
                        )
                    );
        }

        return false;
    }

}
