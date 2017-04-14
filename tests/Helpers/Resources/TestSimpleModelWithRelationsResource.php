<?php
namespace Czim\JsonApi\Test\Helpers\Resources;

use Czim\JsonApi\Support\Resource\AbstractJsonApiResource;

class TestSimpleModelWithRelationsResource extends AbstractJsonApiResource
{

    protected $availableAttributes = [
        'name',
        'active',
        'simple-appended',
    ];

    protected $availableIncludes = [
        'children',
        'single-related',
    ];

    protected $includeRelations = [
        'single-related' => 'related',
    ];

    public function getSimpleAppendedAttribute()
    {
        return 'testing';
    }

}
