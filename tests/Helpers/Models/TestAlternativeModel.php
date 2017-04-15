<?php
namespace Czim\JsonApi\Test\Helpers\Models;

use Illuminate\Database\Eloquent\Model;

class TestAlternativeModel extends Model
{
    protected $fillable = [
        'slug',
        'value',
    ];

    protected $casts = [
        'value' => 'float',
    ];

}
