<?php
namespace Czim\JsonApi\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Container\Container;
use Symfony\Component\HttpFoundation\AcceptHeader;
use Symfony\Component\HttpFoundation\Response;

class JsonApiHeaders
{

    /**
     * @var Container
     */
    protected $app;

    /**
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // check for debugging locally, which should not require the headers
        if ($this->app->environment() === 'local' && $request->input(config('jsonapi.identifiers.request.debug'))) {

            $request->header('Content-type', 'text/html', true);

            return $next($request);
        }


        if ( ! $this->acceptHeaderValid($request)) {
            return response('', 406);
        }

        if ( ! $this->contentTypeHeaderValid(($request))) {
            return response('', 415);
        }

        return $this->addHeader( $next($request) );
    }

    /**
     * @param Request $request
     * @return bool
     */
    protected function acceptHeaderValid(Request $request)
    {
        $acceptHeader = AcceptHeader::fromString($request->header('accept'));

        if ($acceptHeader->has('application/vnd.api+json')) {
            return empty( $acceptHeader->get('application/vnd.api+json')->getAttributes() );
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

    /**
     * Adds the JSON-API Content Type header to the current Response
     *
     * @param Response $response
     * @return mixed
     */
    protected function addHeader(Response $response)
    {
        return $response->header('Content-Type', 'application/vnd.api+json', true);
    }
}
