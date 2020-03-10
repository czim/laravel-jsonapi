<?php
namespace Czim\JsonApi\Enums;

use MyCLabs\Enum\Enum;

/**
 * The root JSON-API type, based on presence of keys and content.
 */
class RootType extends Enum
{
    public const ERROR    = 'error';
    public const META     = 'meta';
    public const RESOURCE = 'resource';
    public const UNKNOWN  = 'unknown';
}
