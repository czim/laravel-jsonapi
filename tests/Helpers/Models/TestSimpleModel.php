<?php
namespace Czim\JsonApi\Test\Helpers\Models;

use Illuminate\Database\Eloquent\Model;

class TestSimpleModel extends Model
{
    protected $fillable = [
        'unique_field',
        'second_field',
        'name',
        'active',
        'hidden',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    // for testing with hide/unhide attributes
    protected $hidden = [
        'hidden',
    ];

    public function children()
    {
        return $this->hasMany(TestRelatedModel::class, 'test_simple_model_id');
    }

    public function related()
    {
        return $this->belongsTo(TestRelatedModel::class, 'test_related_model_id');
    }

}
