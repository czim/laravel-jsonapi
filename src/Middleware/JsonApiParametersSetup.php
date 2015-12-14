<?php
namespace Czim\JsonApi\Middleware;

use Closure;
use Czim\JsonApi\Contracts\JsonApiParametersInterface;
use Czim\JsonApi\Parameters\SortParameter;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

/**
 * This middleware sets up the JsonApiParameters based on analysis of the request
 */
class JsonApiParametersSetup
{

    /**
     * @var JsonApiParametersInterface
     */
    protected $jsonApiParameters;


    /**
     * @param JsonApiParametersInterface $jsonApiParameters
     */
    public function __construct(JsonApiParametersInterface $jsonApiParameters)
    {
        $this->jsonApiParameters = $jsonApiParameters;
    }


    /**
     * Handle an incoming request.
     *
     * @param Request   $request
     * @param Closure   $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // include paths

        $paths = array_map(
            function($v) {
                return camel_case(str_replace('-', '_', $v));
            },
            Arr::where(
                explode(',', $request->input(config('jsonapi.identifiers.request.include'), '')),
                function($key, $value) {
                    return ! empty($value);
                }
            )
        );

        $this->jsonApiParameters->setIncludePaths($paths);


        // filters

        $this->jsonApiParameters->setFilter(
            $request->input(config('jsonapi.identifiers.request.filter'), [])
        );


        // sorting

        $parameters = Arr::where(
            explode(',', $request->input(config('jsonapi.identifiers.request.sort'), '')),
            function($key, $value) {
                return ! empty($value);
            }
        );

        foreach ($parameters as $parameter) {

            $field     = $parameter;
            $direction = 'asc';

            if (substr($field, 0, 1) === '-') {
                $field     = substr($field, 1);
                $direction = 'desc';
            }

            $this->jsonApiParameters->addSortParameter(
                new SortParameter($field, $direction)
            );
        }


        return $next($request);
    }

}
