<?php
namespace Czim\JsonApi\Test\Helpers\Resources;

use Czim\JsonApi\Support\Resource\AbstractEloquentResource;

class TestCommentResource extends AbstractEloquentResource
{
    protected $availableAttributes = [
        'title',
        'body',
        'description',
    ];

    protected $availableIncludes = [
        'author',
        'post',
        'seos',
    ];
}
