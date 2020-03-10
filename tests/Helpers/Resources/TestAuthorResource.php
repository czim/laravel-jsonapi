<?php
namespace Czim\JsonApi\Test\Helpers\Resources;

use Czim\JsonApi\Support\Resource\AbstractEloquentResource;

class TestAuthorResource extends AbstractEloquentResource
{
    protected $availableAttributes = [
        'name',
    ];

    protected $availableIncludes = [
        'posts',
        'comments',
    ];
}
