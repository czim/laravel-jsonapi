<?php
namespace Czim\JsonApi\Test\Helpers\Data;

use Czim\JsonApi\Data\AbstractDataObject;
use Czim\JsonApi\Data\Link;
use Czim\JsonApi\Data\Meta;
use Czim\JsonApi\Data\Resource;

class TestData extends AbstractDataObject
{

    protected $objects = [
        'meta'      => Meta::class,
        'link'      => Link::class . '!',
        'resources' => Resource::class . '[]',
    ];

}
