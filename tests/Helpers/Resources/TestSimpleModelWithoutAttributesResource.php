<?php
namespace Czim\JsonApi\Test\Helpers\Resources;

use Czim\JsonApi\Support\Resource\AbstractEloquentResource;

class TestSimpleModelWithoutAttributesResource extends AbstractEloquentResource
{

    protected $availableAttributes = [];

}
