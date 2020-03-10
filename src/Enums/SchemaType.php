<?php
namespace Czim\JsonApi\Enums;

use MyCLabs\Enum\Enum;

class SchemaType extends Enum
{
    public const CREATE   = 'create';
    public const REQUEST  = 'request';
    public const RESPONSE = 'response';
}
