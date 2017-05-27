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

        // If no prefix is available, or it cannot be stripped from the resource's namespace,
        // the namespace should just default to the top-level type.
        if ( ! $prefix || ! starts_with($classname, $prefix)) {
            return $resource->type();
        }

        $classname = ltrim(substr($classname, strlen($prefix)), '\\');

        // Dasherize path elements
        $segments = explode('\\', $classname);
        $segments = array_map(function ($segment) { return snake_case($segment, '-'); }, $segments);

        // The final segment should not be trusted, but replaced with the resource type,
        // to avoid creating paths that don't match up with defined resource types.
        array_pop($segments);

        $segments[] = $resource->type();

        return implode('/', $segments);
    }

}

