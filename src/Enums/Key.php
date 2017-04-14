<?php
namespace Czim\JsonApi\Enums;

use MyCLabs\Enum\Enum;

class Key extends Enum
{
    const ATTRIBUTES    = 'attributes';
    const DATA          = 'data';
    const ERRORS        = 'errors';
    const INCLUDED      = 'included';
    const LINKS         = 'links';
    const LINK_SELF     = 'self';
    const LINK_RELATED  = 'related';
    const META          = 'meta';
    const PAGE_FIRST    = 'first';
    const PAGE_LAST     = 'last';
    const PAGE_PREV     = 'prev';
    const PAGE_NEXT     = 'next';
    const RELATIONSHIPS = 'relationships';
}
