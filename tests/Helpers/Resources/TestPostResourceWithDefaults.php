<?php
namespace Czim\JsonApi\Test\Helpers\Resources;

use Czim\JsonApi\Support\Resource\AbstractEloquentResource;

class TestPostResourceWithDefaults extends AbstractEloquentResource
{
    protected $availableAttributes = [
        'title',
        'body',
        'type',
        'checked',
        'description-adjusted',
    ];

    protected $availableIncludes = [
        'comments',
        'main-author',
        'seo',
    ];

    protected $includeRelations = [
        'main-author' => 'author',
    ];

    protected $defaultIncludes = [
        'main-author',
        'seo',
    ];

    public function getSimpleAppendedAttribute()
    {
        return 'testing';
    }

    public function getDescriptionAdjustedAttribute()
    {
        return 'Prefix: ' . $this->model->description;
    }
}
