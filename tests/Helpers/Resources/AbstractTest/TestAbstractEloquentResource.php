<?php
namespace Czim\JsonApi\Test\Helpers\Resources\AbstractTest;

use Czim\JsonApi\Support\Resource\AbstractEloquentResource;

class TestAbstractEloquentResource extends AbstractEloquentResource
{
    protected $availableAttributes = [
        'name',
        'title',
        'accessor',
        'date_accessor',
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

    protected $dateAttributes = [
        'date-accessor',
    ];

    protected $dateAttributeFormats = [
        'updated-at'    => 'Y-m-d H:i',
        'date-accessor' => 'Y-m-d',
    ];

    public function getAccessorAttribute()
    {
        return 'custom';
    }

    public function getDateAccessorAttribute()
    {
        return '2017-01-02 03:04:05';
    }
}
