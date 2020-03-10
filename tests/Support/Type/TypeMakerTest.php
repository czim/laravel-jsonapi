<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\JsonApi\Test\Support\Type;

use Czim\JsonApi\Enums\RootType;
use Czim\JsonApi\Support\Type\TypeMaker;
use Czim\JsonApi\Test\Helpers\Models\TestSimpleModel;
use Czim\JsonApi\Test\TestCase;
use InvalidArgumentException;

class TypeMakerTest extends TestCase
{

    /**
     * @test
     */
    function it_makes_a_type_for_a_model_instance()
    {
        $maker = new TypeMaker;
        $model = new TestSimpleModel;

        static::assertEquals('test-simple-models', $maker->makeFor($model));
    }

    /**
     * @test
     */
    function it_makes_a_type_for_a_model_fqn()
    {
        $maker = new TypeMaker;

        static::assertEquals('test-simple-models', $maker->makeFor(TestSimpleModel::class));
    }


    /**
     * @test
     */
    function it_makes_a_type_from_an_object()
    {
        $maker = new TypeMaker;

        static::assertEquals('root-types', $maker->makeFor(new RootType('meta')));
    }

    /**
     * @test
     */
    function it_makes_a_type_from_a_string()
    {
        $maker = new TypeMaker;

        static::assertEquals('some-string-here', $maker->makeFor('SomeStringHere'));
    }

    /**
     * @test
     */
    function it_throws_an_exception_if_it_cannot_make_a_type()
    {
        $this->expectException(InvalidArgumentException::class);

        $maker = new TypeMaker;

        $maker->makeFor(['some', 'array']);
    }


    // ------------------------------------------------------------------------------
    //      Model
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_dasherizes_and_pluralizes_a_model_class_name()
    {
        $maker = new TypeMaker;

        static::assertEquals('test-simple-models', $maker->makeForModelClass(TestSimpleModel::class));
    }

    /**
     * @test
     */
    function it_can_use_the_entire_classname_for_empty_parameter()
    {
        $maker = new TypeMaker;

        static::assertEquals('czim--json-api--test--helpers--models--test-simple-models', $maker->makeForModelClass(TestSimpleModel::class, ''));
    }

    /**
     * @test
     */
    function it_can_trim_part_of_the_classname_given_as_parameter()
    {
        $maker = new TypeMaker;

        static::assertEquals('test--helpers--models--test-simple-models', $maker->makeForModelClass(TestSimpleModel::class, 'Czim\\JsonApi\\'));
    }

    /**
     * @test
     */
    function it_uses_config_value_to_trim_classname_by_default()
    {
        $this->app['config']->set('jsonapi.transform.type.trim-namespace', 'Czim\\JsonApi\\Test');

        $maker = new TypeMaker;

        static::assertEquals('helpers--models--test-simple-models', $maker->makeForModelClass(TestSimpleModel::class));
    }

}
