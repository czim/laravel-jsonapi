<?php
namespace Czim\JsonApi\Test\Helpers\Resources;

use Czim\JsonApi\Support\Resource\AbstractJsonApiResource;

class TestSeoResource extends AbstractJsonApiResource
{

    protected $availableAttributes = [
        'slug',
    ];

    protected $availableIncludes = [
        'seoable',
    ];

}
