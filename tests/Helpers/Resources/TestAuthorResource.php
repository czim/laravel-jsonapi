<?php
namespace Czim\JsonApi\Test\Helpers\Resources;

use Czim\JsonApi\Support\Resource\AbstractJsonApiResource;

class TestAuthorResource extends AbstractJsonApiResource
{

    protected $availableAttributes = [
        'name',
    ];

    protected $availableIncludes = [
        'posts',
        'comments',
    ];

}
