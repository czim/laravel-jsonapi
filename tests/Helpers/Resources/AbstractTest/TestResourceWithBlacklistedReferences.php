<?php
namespace Czim\JsonApi\Test\Helpers\Resources\AbstractTest;

class TestResourceWithBlacklistedReferences extends AbstractTestResource
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
