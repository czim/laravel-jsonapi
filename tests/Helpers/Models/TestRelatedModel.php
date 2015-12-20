<?php
namespace Czim\JsonApi\Test\Helpers\Models;

use Czim\JsonApi\Contracts\ResourceInterface;
use Czim\JsonApi\Encoding\JsonApiResourceEloquentTrait;
use Illuminate\Database\Eloquent\Model;

class TestRelatedModel extends Model implements ResourceInterface
{
    use JsonApiResourceEloquentTrait;

    protected $fillable = [
        'name',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function testModel()
    {
        return $this->belongsTo(TestModel::class);
    }

}
