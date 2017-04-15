<?php
namespace Czim\JsonApi\Test\Helpers\Resources\AbstractTest;

use Czim\JsonApi\Support\Resource\AbstractJsonApiResource;

class TestResourceWithBlacklistedReferences extends AbstractJsonApiResource
{

    protected $availableIncludes = [
        'comments',
        'post',
        'seo',
    ];

    protected $excludeReferences = [
        'comments',
    ];

}
