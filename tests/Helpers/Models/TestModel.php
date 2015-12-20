<?php
namespace Czim\JsonApi\Test\Helpers\Models;

use Czim\JsonApi\Contracts\ResourceInterface;
use Czim\JsonApi\Encoding\JsonApiResourceEloquentTrait;
use Illuminate\Database\Eloquent\Model;

class TestModel extends Model implements ResourceInterface
{
    use JsonApiResourceEloquentTrait;

    protected $fillable = [
        'name',
        'description',
        'number',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function testRelatedModels()
    {
        return $this->hasMany(TestRelatedModel::class);
    }

}
