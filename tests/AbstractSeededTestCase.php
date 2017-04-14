<?php
namespace Czim\JsonApi\Test;

use Czim\JsonApi\Test\Helpers\Models\TestSimpleModel;
use Czim\JsonApi\Test\Helpers\Models\TestRelatedModel;
use Illuminate\Support\Facades\Schema;

abstract class AbstractSeededTestCase extends DatabaseTestCase
{

    protected function migrateDatabase()
    {
        Schema::create('test_simple_models', function($table) {
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

        Schema::create('test_related_models', function($table) {
            /** @var \Illuminate\Database\Schema\Blueprint $table */
            $table->increments('id');
            $table->string('name', 255);
            $table->integer('test_simple_model_id')->nullable();
            $table->nullableTimestamps();
        });
    }


    protected function seedDatabase()
    {
        $this->seedSimpleModels()
             ->seedRelatedModels();
    }


    protected function seedSimpleModels()
    {
        TestSimpleModel::create([
            'name'         => 'Test A',
            'unique_field' => 'test-a',
            'second_field' => 'testing something',
            'active'       => true,
            'hidden'       => 'okay',
        ]);

        TestSimpleModel::create([
            'name'         => 'Test B',
            'unique_field' => 'test-b',
            'second_field' => 'another testing',
            'active'       => false,
            'hidden'       => 'done',
        ]);

        return $this;
    }

    protected function seedRelatedModels()
    {
        $related = new TestRelatedModel([
            'name' => 'Related X',
        ]);
        $related->parent()->associate(TestSimpleModel::first());
        $related->save();

        $related->simples()->save(TestSimpleModel::skip(1)->first());

        $related = new TestRelatedModel([
            'name' => 'Related Y',
        ]);
        $related->parent()->associate(TestSimpleModel::first());
        $related->save();

        $related = new TestRelatedModel([
            'name' => 'Related Z',
        ]);
        $related->parent()->associate(TestSimpleModel::first());
        $related->save();

        return $this;
    }

}
