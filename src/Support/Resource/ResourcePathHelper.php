<?php
namespace Czim\JsonApi\Support\Resource;

use Czim\JsonApi\Contracts\Resource\ResourceInterface;

/**
 * Class ResourcePathHelper
 *
 * Helps deal with paths for resources, based on (relative) namespaces
 * and standardized dasherization for URLs.
 */
class ResourcePathHelper
{

    /**
     * Makes a relative URL path for a given resource.
     *
     * @param ResourceInterface $resource
     * @return string
     */
    public function makePath(ResourceInterface $resource)
    {
        $classname = get_class($resource);

        $prefix = config('jsonapi.repository.resource.namespace');

        if ($prefix && starts_with($classname, $prefix)) {
            $classname = ltrim(substr($classname, strlen($prefix)), '\\');
        }

        // Dasherize path elements
        $segments = explode('\\', $classname);
        $segments = array_map(function ($segment) { return snake_case($segment, '-'); }, $segments);

        return implode('/', $segments);
    }

}

