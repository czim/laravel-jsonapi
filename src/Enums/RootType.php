<?php
namespace Czim\JsonApi\Enums;

use MyCLabs\Enum\Enum;

/**
 * The root JSON-API type, based on presence of keys and content.
 */
class RootType extends Enum
{
    const ERROR    = 'error';
    const META     = 'meta';
    const RESOURCE = 'resource';
    const UNKNOWN  = 'unknown';
}
