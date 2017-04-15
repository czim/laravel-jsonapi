<?php
namespace Czim\JsonApi\Test\Helpers\Resources;

use Czim\JsonApi\Support\Resource\AbstractJsonApiResource;

class TestCommentResource extends AbstractJsonApiResource
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
