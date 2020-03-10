<?php
namespace Czim\JsonApi\Test\Helpers\Resources;

use Czim\JsonApi\Support\Resource\AbstractEloquentResource;

class TestSeoResource extends AbstractEloquentResource
{
    protected $availableAttributes = [
        'slug',
    ];

    protected $availableIncludes = [
        'seoable',
    ];
}
