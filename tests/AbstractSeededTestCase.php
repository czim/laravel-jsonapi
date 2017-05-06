<?php
namespace Czim\JsonApi\Test;

use Czim\JsonApi\Test\Helpers\Models\TestAuthor;
use Czim\JsonApi\Test\Helpers\Models\TestComment;
use Czim\JsonApi\Test\Helpers\Models\TestPost;
use DB;
use Illuminate\Support\Facades\Schema;

abstract class AbstractSeededTestCase extends DatabaseTestCase
{

    protected function migrateDatabase()
    {
        Schema::create('test_simple_models', function ($table) {
            /** @var \Illuminate\Database\Schema\Blueprint $table */
            $table->increments('id');
            $table->string('unique_field', 255);
            $table->string('second_field', 255);
            $table->string('name', 50);
            $table->boolean('active')->default(false);
            $table->string('hidden', 50);
            $table->integer('test_related_model_id')->nullable();
            $table->nullableTimestamps();
        });

        Schema::create('test_authors', function ($table) {
            /** @var \Illuminate\Database\Schema\Blueprint $table */
            $table->increments('id');
            $table->string('name', 255);
            $table->enum('gender', ['m', 'f'])->default('f');
            $table->string('image_file_name')->nullable();
            $table->integer('image_file_size')->nullable();
            $table->string('image_content_type')->nullable();
            $table->timestamp('image_updated_at')->nullable();
            $table->nullableTimestamps();
        });

        Schema::create('test_posts', function ($table) {
            /** @var \Illuminate\Database\Schema\Blueprint $table */
            $table->increments('id');
            $table->integer('test_author_id')->nullable()->unsigned();
            $table->integer('test_genre_id')->nullable()->unsigned();
            $table->string('title', 50);
            $table->text('body');
            $table->string('description', 255)->nullable();
            $table->enum('type', ['announcement', 'news', 'notice', 'periodical'])->default('news');
            $table->boolean('checked')->default(false);
            $table->nullableTimestamps();
        });

        Schema::create('test_comments', function ($table) {
            /** @var \Illuminate\Database\Schema\Blueprint $table */
            $table->increments('id');
            $table->integer('test_post_id')->unsigned();
            $table->integer('test_author_id')->nullable()->unsigned();
            $table->string('title', 50)->nullable();
            $table->text('body')->nullable();
            $table->string('description', 255)->nullable();
            $table->nullableTimestamps();
        });

        Schema::create('test_seos', function ($table) {
            /** @var \Illuminate\Database\Schema\Blueprint $table */
            $table->increments('id');
            $table->integer('seoable_id')->unsigned()->nullable();
            $table->string('seoable_type', 255)->nullable();
            $table->string('slug', 255);
            $table->nullableTimestamps();
        });

        Schema::create('post_related', function ($table) {
            /** @var \Illuminate\Database\Schema\Blueprint $table */
            $table->increments('id');
            $table->integer('from_id')->unsigned()->nullable();
            $table->integer('to_id')->unsigned()->nullable();
        });

        Schema::create('post_pivot_related', function ($table) {
            /** @var \Illuminate\Database\Schema\Blueprint $table */
            $table->increments('id');
            $table->integer('from_id')->unsigned()->nullable();
            $table->integer('to_id')->unsigned()->nullable();
            $table->string('type', 100);
            $table->date('date')->nullable();
            $table->nullableTimestamps();
        });
    }


    protected function seedDatabase()
    {
        $this->seedAuthors()
            ->seedPosts()
            ->seedComments();
    }


    protected function seedAuthors()
    {
        TestAuthor::create([
            'name' => 'Test Testington',
        ]);

        TestAuthor::create([
            'name' => 'Tosti Tortellini Von Testering',
        ]);

        return $this;
    }

    protected function seedPosts()
    {
        $post = new TestPost([
            'title'       => 'Some Basic Title',
            'body'        => 'Lorem ipsum dolor sit amet, egg beater batter pan consectetur adipiscing elit.',
            'type'        => 'notice',
            'checked'     => true,
            'description' => 'the best possible post for testing',
        ]);
        $post->author()->associate(TestAuthor::first());
        $post->save();


        $post = new TestPost([
            'title'       => 'Elaborate Alternative Title',
            'body'        => 'Donec nec metus urna. Tosti pancake frying pan tortellini Fusce ex massa.',
            'type'        => 'news',
            'checked'     => false,
            'description' => 'some alternative testing post',
        ]);
        $post->author()->associate(TestAuthor::first());
        $post->save();


        $post = new TestPost([
            'title'       => 'Surprising Testing Title',
            'body'        => 'Aliquam pancake batter frying pan ut mauris eros.',
            'type'        => 'warning',
            'checked'     => true,
            'description' => 'something else',
        ]);
        $post->author()->associate(TestAuthor::skip(1)->first());
        $post->save();


        // Seed the related pivot tables
        DB::table('post_related')->insert(['from_id' => 1, 'to_id' => 2]);
        DB::table('post_related')->insert(['from_id' => 1, 'to_id' => 3]);

        DB::table('post_pivot_related')->insert(['from_id' => 1, 'to_id' => 2, 'type' => 'a', 'date' => '2017-01-01']);
        DB::table('post_pivot_related')->insert(['from_id' => 1, 'to_id' => 3, 'type' => 'a', 'date' => '2017-02-01']);

        return $this;
    }

    protected function seedComments()
    {
        $comment = new TestComment([
            'title'       => 'Comment Title A',
            'body'        => 'Lorem ipsum dolor sit amet.',
            'description' => 'comment one',
        ]);
        $comment->author()->associate(TestAuthor::skip(1)->first());
        TestPost::find(1)->comments()->save($comment);


        $comment = new TestComment([
            'title'       => 'Comment Title B',
            'body'        => 'Phasellus iaculis velit nec purus rutrum eleifend.',
            'description' => 'comment two',
        ]);
        $comment->author()->associate(TestAuthor::skip(1)->first());
        TestPost::find(1)->comments()->save($comment);


        $comment = new TestComment([
            'title'       => 'Comment Title C',
            'body'        => 'Nam eget magna quis arcu consectetur pellentesque.',
            'description' => 'comment three',
        ]);
        $comment->author()->associate(TestAuthor::first());
        TestPost::find(3)->comments()->save($comment);

        return $this;
    }

}
