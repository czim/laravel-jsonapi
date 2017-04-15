<?php
namespace Czim\JsonApi\Test\Helpers\Resources;

use Czim\JsonApi\Support\Resource\AbstractJsonApiResource;

class TestAlternativeModelResource extends AbstractJsonApiResource
{

    protected $availableAttributes = [
        'slug',
        'value',
    ];

}
