<?php
namespace Czim\JsonApi\Test\Helpers\Models;

use Illuminate\Database\Eloquent\Model;

class TestRelatedModel extends Model
{
    protected $fillable = [
        'name',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(TestSimpleModel::class, 'test_simple_model_id');
    }

    public function simples()
    {
        return $this->hasMany(TestSimpleModel::class, 'test_related_model_id');
    }

}
