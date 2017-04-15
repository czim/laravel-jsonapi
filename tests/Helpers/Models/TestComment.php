<?php
namespace Czim\JsonApi\Test\Helpers\Models;

use Illuminate\Database\Eloquent\Model;

class TestComment extends Model
{
    protected $fillable = [
        'title',
        'body',
        'description',
    ];

    public function author()
    {
        return $this->belongsTo(TestAuthor::class, 'test_author_id');
    }

    public function post()
    {
        return $this->belongsTo(TestPost::class, 'test_post_id');
    }

    public function seos()
    {
        return $this->morphMany(TestSeo::class, 'seoable');
    }

}
