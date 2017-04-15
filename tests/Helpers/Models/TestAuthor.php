<?php
namespace Czim\JsonApi\Test\Helpers\Models;

use Illuminate\Database\Eloquent\Model;

class TestAuthor extends Model
{
    protected $fillable = [
        'name',
        'image',
    ];

    public function posts()
    {
        return $this->hasMany(TestPost::class);
    }

    public function comments()
    {
        return $this->hasMany(TestComment::class);
    }

}
