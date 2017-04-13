<?php
namespace Czim\JsonApi\Enums;

use MyCLabs\Enum\Enum;

class SchemaType extends Enum
{
    const CREATE   = 'create';
    const REQUEST  = 'request';
    const RESPONSE = 'response';
}
