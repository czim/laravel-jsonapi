<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\JsonApi\Test\Support\Validation;

use Czim\JsonApi\Enums\SchemaType;
use Czim\JsonApi\Support\Validation\JsonApiValidator;
use Czim\JsonApi\Test\TestCase;
use Illuminate\Support\MessageBag;

class JsonApiValidatorTest extends TestCase
{

    /**
     * @test
     */
    function it_validates_create_json_api_data()
    {
        $validator = new JsonApiValidator;

        // Valid data
        static::assertTrue($validator->validateSchema($this->getValidCreateData(), SchemaType::CREATE));

        $errors = $validator->getErrors();
        static::assertInstanceOf(MessageBag::class, $errors);
        static::assertTrue($errors->isEmpty());

        // Invalid data
        static::assertFalse($validator->validateSchema($this->getInvalidCreateData(), SchemaType::CREATE));

        $errors = $validator->getErrors();
        static::assertInstanceOf(MessageBag::class, $errors);
        static::assertFalse($errors->isEmpty());
    }

    /**
     * @test
     */
    function it_validates_create_json_api_data_with_empty_attributes()
    {
        $validator = new JsonApiValidator;

        // Valid data
        static::assertTrue($validator->validateSchema($this->getValidCreateDataWithEmptyAttributes(), SchemaType::CREATE));

        $errors = $validator->getErrors();
        static::assertInstanceOf(MessageBag::class, $errors);
        static::assertTrue($errors->isEmpty());
    }

    /**
     * @test
     */
    function it_validates_normal_request_json_api_data()
    {
        $validator = new JsonApiValidator;

        // Valid data
        static::assertTrue($validator->validateSchema($this->getValidRequestData()));

        $errors = $validator->getErrors();
        static::assertInstanceOf(MessageBag::class, $errors);
        static::assertTrue($errors->isEmpty());

        // Invalid data
        static::assertFalse($validator->validateSchema($this->getInvalidRequestData()));

        $errors = $validator->getErrors();
        static::assertInstanceOf(MessageBag::class, $errors);
        static::assertFalse($errors->isEmpty());
    }

    /**
     * @test
     */
    function it_returns_empty_message_bag_for_errors_before_validation()
    {
        $validator = new JsonApiValidator;

        $errors = $validator->getErrors();

        static::assertInstanceOf(MessageBag::class, $errors);
        static::assertTrue($errors->isEmpty());
    }

    /**
     * @return array
     */
    protected function getValidCreateData()
    {
        return json_decode('{
                "data": {
                    "type": "photos",
                    "attributes": {
                        "title": "Ember Hamster",
                        "src": "http://example.com/images/productivity.png"
                    },
                    "relationships": {
                        "photographer": {
                            "data": { "type": "people", "id": "9" }
                        }
                    }
                }
            }',
        true);
    }

    /**
     * @return array
     */
    protected function getValidCreateDataWithEmptyAttributes()
    {
        return json_decode('{
                "data": {
                    "type": "photos",
                    "attributes": {
                    },
                    "relationships": {
                        "photographer": {
                            "data": { "type": "people", "id": "9" }
                        }
                    }
                }
            }',
            true);
    }

    /**
     * @return array
     */
    protected function getInvalidCreateData()
    {
        return json_decode('{
                "data": {
                    "type": 3425,
                    "relationships": "test"
                }
            }',
            true);
    }

    /**
     * @return array
     */
    protected function getValidRequestData()
    {
        return json_decode('{
                "data": {
                    "id": "324",
                    "type": "photos",
                    "attributes": {
                        "title": "Ember Hamster",
                        "src": "http://example.com/images/productivity.png"
                    },
                    "relationships": {
                        "photographer": {
                            "data": { "type": "people", "id": "9" }
                        }
                    }
                }
            }',
            true);
    }

    /**
     * @return array
     */
    protected function getInvalidRequestData()
    {
        return $this->getValidCreateData();
    }

}
