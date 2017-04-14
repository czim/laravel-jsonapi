<?php
namespace Czim\JsonApi\Test\Support\Type;

use Czim\JsonApi\Support\Type\TypeMaker;
use Czim\JsonApi\Test\Helpers\Models\TestSimpleModel;
use Czim\JsonApi\Test\TestCase;

class TypeMakerTest extends TestCase
{

    /**
     * @test
     */
    function it_dasherizes_and_pluralizes_a_model_class_name()
    {
        $maker = new TypeMaker;
        $model = new TestSimpleModel;

        static::assertEquals('test-simple-models', $maker->makeForModel($model));
    }
    
    /**
     * @test
     */
    function it_can_use_the_entire_classname_for_empty_parameter()
    {
        $maker = new TypeMaker;
        $model = new TestSimpleModel;

        static::assertEquals('czim--json-api--test--helpers--models--test-simple-models', $maker->makeForModel($model, ''));
    }

    /**
     * @test
     */
    function it_can_trim_part_of_the_classname_given_as_parameter()
    {
        $maker = new TypeMaker;
        $model = new TestSimpleModel;

        static::assertEquals('test--helpers--models--test-simple-models', $maker->makeForModel($model, 'Czim\\JsonApi\\'));
    }

    /**
     * @test
     */
    function it_uses_config_value_to_trim_classname_by_default()
    {
        $this->app['config']->set('jsonapi.transform.type.trim-namespace', 'Czim\\JsonApi\\Test');

        $maker = new TypeMaker;
        $model = new TestSimpleModel;

        static::assertEquals('helpers--models--test-simple-models', $maker->makeForModel($model));
    }

}
