<?php
namespace Czim\JsonApi\Enums;

use MyCLabs\Enum\Enum;

class Key extends Enum
{
    public const ATTRIBUTES    = 'attributes';
    public const DATA          = 'data';
    public const ERRORS        = 'errors';
    public const INCLUDED      = 'included';
    public const LINKS         = 'links';
    public const LINK_SELF     = 'self';
    public const LINK_RELATED  = 'related';
    public const META          = 'meta';
    public const PAGE_FIRST    = 'first';
    public const PAGE_LAST     = 'last';
    public const PAGE_PREV     = 'prev';
    public const PAGE_NEXT     = 'next';
    public const RELATIONSHIPS = 'relationships';
}
