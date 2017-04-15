<?php
namespace Czim\JsonApi\Test\Helpers\Resources\AbstractTest;

use Czim\JsonApi\Support\Resource\AbstractJsonApiResource;

class TestAbstractResource extends AbstractJsonApiResource
{

    protected $availableAttributes = [
        'name',
        'title',
        'accessor',
    ];

    protected $availableIncludes = [
        'comments',
        'alternative-key',
        'not-a-relation',
        'method-does-not-exist',
    ];

    protected $includeRelations = [
        'alternative-key' => 'comments',
        'not-a-relation'  => 'testMethod',
    ];

    protected $defaultIncludes = [
        'comments',
    ];

    protected $includeReferences = [
        'comments',
    ];

    protected $availableFilters = [
        'some-filter',
        'test',
    ];

    protected $defaultFilters = [
        'some-filter' => 13,
    ];

    protected $availableSortAttributes = [
        'title',
        'id',
    ];

    protected $defaultSortAttributes = [
        '-id',
    ];

    public function getAccessorAttribute()
    {
        return 'custom';
    }

}
