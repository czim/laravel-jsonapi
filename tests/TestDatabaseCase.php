<?php
namespace Czim\JsonApi\Test;

use Illuminate\Support\Facades\Schema;

abstract class TestDatabaseCase extends TestCase
{

    public function setUp()
    {
        parent::setUp();

        $this->migrateDatabase();
        $this->seedDatabase();
    }


    protected function migrateDatabase()
    {
        // model we can test anything but translations with
        Schema::create('test_models', function($table) {
            $table->increments('id');
            $table->string('name', 20);
            $table->string('description', 255);
            $table->integer('number')->unsigned();
            $table->timestamps();
        });

        Schema::create('test_related_models', function($table) {
            $table->increments('id');
            $table->string('name', 20);
            $table->integer('test_model_id')->unsigned()->nullable();
            $table->timestamps();
        });

    }

    abstract protected function seedDatabase();

}
