<?php
namespace Czim\JsonApi\Test\Helpers\Resources;

use Czim\JsonApi\Support\Resource\AbstractJsonApiResource;

class TestPostResource extends AbstractJsonApiResource
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

    public function getSimpleAppendedAttribute()
    {
        return 'testing';
    }

    public function getDescriptionAdjustedAttribute()
    {
        return 'Prefix: ' . $this->model->description;
    }

}
