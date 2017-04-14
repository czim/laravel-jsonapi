<?php
namespace Czim\JsonApi\Test\Helpers\Resources;

use Czim\JsonApi\Support\Resource\AbstractJsonApiResource;

class TestSimpleModelResource extends AbstractJsonApiResource
{

    protected $availableAttributes = [
        'unique_field',
        'second_field',
        'name',
        'active',
    ];

}
