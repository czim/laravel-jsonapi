<?php
namespace Czim\JsonApi\Test\Helpers\Resources\AbstractTest;

class TestResourceWithNoReferences extends AbstractTestResource
{

    protected $availableIncludes = [
        'comments',
        'post',
        'seo',
    ];

    protected $includeReferences = false;

}
