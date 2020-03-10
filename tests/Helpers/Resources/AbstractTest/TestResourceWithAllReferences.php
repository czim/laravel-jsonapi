<?php
namespace Czim\JsonApi\Test\Helpers\Resources\AbstractTest;

class TestResourceWithAllReferences extends AbstractTestResource
{
    protected $availableIncludes = [
        'comments',
        'post',
        'seo',
    ];

    protected $includeReferences = true;
}
