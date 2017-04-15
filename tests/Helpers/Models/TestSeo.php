<?php
namespace Czim\JsonApi\Test\Helpers\Models;

use Illuminate\Database\Eloquent\Model;

class TestSeo extends Model
{
    protected $fillable = [
        'slug',
    ];

    /**
     * @see \Czim\JsonApi\Test\Helpers\Models\TestPost
     * @see \Czim\JsonApi\Test\Helpers\Models\TestComment
     */
    public function seoable()
    {
        return $this->morphTo('seoable');
    }

}
