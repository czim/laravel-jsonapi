<?php
namespace Czim\JsonApi\Test\Helpers\Resources;

use Czim\JsonApi\Support\Resource\AbstractEloquentResource;

class TestSimpleModelResource extends AbstractEloquentResource
{
    protected $availableAttributes = [
        'unique_field',
        'second_field',
        'name',
        'active',
    ];
}
