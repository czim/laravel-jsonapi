<?php
namespace Czim\JsonApi\Test\Helpers\Resources;

use Czim\JsonApi\Support\Resource\AbstractJsonApiResource;

class TestRelatedModelResource extends AbstractJsonApiResource
{

    protected $availableAttributes = [
        'name',
    ];

    protected $availableIncludes = [
        'simples',
        'parent',
    ];

}
