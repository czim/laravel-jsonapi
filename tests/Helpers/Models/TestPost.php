<?php
namespace Czim\JsonApi\Test\Helpers\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class TestPost
 *
 * @property string $title
 * @property string $body
 * @property string $type
 * @property bool   $checked
 * @property string $description
 */
class TestPost extends Model
{
    protected $fillable = [
        'title',
        'body',
        'type',
        'checked',
        'description',
    ];

    protected $casts = [
        'checked' => 'boolean',
    ];

    public $test = false;


    public function author()
    {
        return $this->belongsTo(TestAuthor::class, 'test_author_id');
    }

    public function comments()
    {
        return $this->hasMany(TestComment::class);
    }

    public function seo()
    {
        return $this->morphOne(TestSeo::class, 'seoable');
    }

    public function related()
    {
        return $this->belongsToMany(TestPost::class, 'post_related', 'from_id', 'to_id');
    }

    public function pivotRelated()
    {
        return $this->belongsToMany(TestPost::class, 'post_pivot_related', 'from_id', 'to_id')
            ->withPivot([
                'type',
                'date',
            ])
            ->withTimestamps();
    }

    /**
     * @return string
     */
    public function testMethod()
    {
        return 'testing method value';
    }

}
