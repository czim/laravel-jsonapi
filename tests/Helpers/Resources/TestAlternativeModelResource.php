<?php
namespace Czim\JsonApi\Test\Helpers\Resources;

use Czim\JsonApi\Support\Resource\AbstractEloquentResource;

class TestAlternativeModelResource extends AbstractEloquentResource
{
    protected $availableAttributes = [
        'slug',
        'value',
    ];
}
