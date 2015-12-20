<?php
namespace Czim\JsonApi\Test;

use Czim\JsonApi\Encoding\JsonApiEncoder;
use Czim\JsonApi\Test\Helpers\Models\TestModel;
use Czim\JsonApi\Test\Helpers\Models\TestRelatedModel;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\MessageBag;
use Symfony\Component\HttpKernel\Exception\HttpException;

class JsonApiEncoderTest extends TestDatabaseCase
{

    /**
     * @var JsonApiEncoder
     */
    protected $encoder;


    public function setUp()
    {
        parent::setUp();

        $this->encoder = app(JsonApiEncoder::class);
    }

    protected function seedDatabase()
    {
        $model = TestModel::create([
            'name'        => 'Test Model A',
            'description' => 'Description for Model A',
            'number'      => 13,
        ]);

        $relatedOne = TestRelatedModel::create([
            'name' => 'Test Related Model 1',
        ]);

        $relatedTwo = TestRelatedModel::create([
            'name' => 'Test Related Model 2',
        ]);

        $relatedThree = TestRelatedModel::create([
            'name' => 'Alternative Name',
        ]);

        $model->TestRelatedModels()->save($relatedOne);
        $model->TestRelatedModels()->save($relatedTwo);
        $model->TestRelatedModels()->save($relatedThree);
    }




    // ------------------------------------------------------------------------------
    //      Encoding of Data
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_encodes_a_response_for_a_resource_model_without_relationships()
    {
        $model = TestModel::find(1);

        $response = $this->encoder->response( $model );

        $result = json_decode($response->getContent(), true);

        $this->assertEquals([
            "type"       => "test-models",
            "id"         => "1",
            "attributes" => [
                "name"        => "Test Model A",
                "description" => "Description for Model A",
                "number"      => "13",
            ],
            "links" => [
                "self" => "http://www.test.com/v1/test-models/1"
            ],
        ], $result['data'], "Response content mismatch");
    }

    /**
     * @test
     */
    function it_encodes_a_response_for_a_resource_model_with_relationships()
    {
        $model = TestModel::find(1);

        $model->load('testRelatedModels');

        $response = $this->encoder->response( $model );

        $result = json_decode($response->getContent(), true);

        $this->assertEquals([
            "type"       => "test-models",
            "id"         => "1",
            "attributes" => [
                "name"        => "Test Model A",
                "description" => "Description for Model A",
                "number"      => "13",
            ],
            "relationships" => [
                'test-related-models' => [
                    'data' => [
                        [ 'type' => 'test-related-models', 'id' => '1' ],
                        [ 'type' => 'test-related-models', 'id' => '2' ],
                        [ 'type' => 'test-related-models', 'id' => '3' ],
                    ],
                    'links' => [
                        'related' => 'http://www.test.com/v1/test-models/1/test-related-models',
                    ]
                ],
            ],
            "links" => [
                "self" => "http://www.test.com/v1/test-models/1"
            ],
        ], $result['data'], "Response content mismatch");
    }

    /**
     * @test
     */
    function it_encodes_a_response_for_a_collection_of_resource_models()
    {
        $models = TestRelatedModel::whereHas('testModel', function($query) {
            return $query->where('id', 1);
        })->get();

        $response = $this->encoder->response( $models );

        $result = json_decode($response->getContent(), true);

        $this->assertCount(3, $result['data'], 'Result resource count should be 3');
        $this->assertEquals([
            [
                "type"       => "test-related-models",
                "id"         => "1",
                "attributes" => [
                    "name"          => "Test Related Model 1",
                    "test-model-id" => "1",
                ],
                "links" => [
                    "self" => "http://www.test.com/v1/test-related-models/1"
                ],
            ],
            [
                "type"       => "test-related-models",
                "id"         => "2",
                "attributes" => [
                    "name"          => "Test Related Model 2",
                    "test-model-id" => "1",
                ],
                "links" => [
                    "self" => "http://www.test.com/v1/test-related-models/2"
                ],
            ],
            [
                "type"       => "test-related-models",
                "id"         => "3",
                "attributes" => [
                    "name"          => "Alternative Name",
                    "test-model-id" => "1",
                ],
                "links" => [
                    "self" => "http://www.test.com/v1/test-related-models/3"
                ],
            ],
        ], $result['data'], "Response content mismatch");
    }


    // ------------------------------------------------------------------------------
    //      Encoding of Errors
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_encodes_errors_for_a_string()
    {
        $response = $this->encoder->errors('testing error string');

        $this->assertInstanceOf(Response::class, $response);

        $result = json_decode($response->getContent(), true);

        $this->assertCount(1, $result['errors'], "Error list should have 1 result");
        $this->assertEquals([
            "status" => "500",
            "title"  => "testing error string",
        ], $result['errors'][0], "Response content mismatch");
    }

    /**
     * @test
     */
    function it_encodes_errors_for_an_exception()
    {
        $response = $this->encoder->errors(
            new Exception('testing error string in exception', 123)
        );

        $this->assertInstanceOf(Response::class, $response);

        $result = json_decode($response->getContent(), true);

        $this->assertCount(1, $result['errors'], "Error list should have 1 result");
        $this->assertEquals([
            "code"   => "123",
            "title"  => "testing error string in exception",
            "status" => "500",
        ], $result['errors'][0], "Response content mismatch for normal exception");


        // http exception with status code

        $response = $this->encoder->errors(
            new HttpException('444', 'testing error string with status code', null, [], 123)
        );

        $this->assertInstanceOf(Response::class, $response);

        $result = json_decode($response->getContent(), true);

        $this->assertCount(1, $result['errors'], "Error list should have 1 result");
        $this->assertEquals([
            "status" => "444",
            "title"  => "testing error string with status code",
            "code"   => "123",
        ], $result['errors'][0], "Response content mismatch for http-exception");
    }

    /**
     * @test
     */
    function it_encodes_errors_for_validation_messages()
    {
        $messages = new MessageBag([
            'test' => [ [ 'The test must be an integer' ], [ 'The test must be at least 100' ] ]
        ]);

        $response = $this->encoder->errors($messages, 422);

        $this->assertInstanceOf(Response::class, $response);

        $result = json_decode($response->getContent(), true);

        $this->assertCount(2, $result['errors'], "Error list should have 1 result");
        $this->assertEquals([
            [
                "status" => "422",
                "title"  => "The test must be an integer",
            ],
            [
                "status" => "422",
                "title"  => "The test must be at least 100",
            ]
        ], $result['errors'], "Response content mismatch");
    }

    /**
     * @test
     */
    function it_encodes_errors_for_an_array_of_things()
    {
        $response = $this->encoder->errors([
            'testing error string',
            new Exception('testing error string in exception', 123)
        ]);

        $this->assertInstanceOf(Response::class, $response);

        $result = json_decode($response->getContent(), true);

        $this->assertCount(2, $result['errors'], "Error list should have 1 result");
        $this->assertEquals([
            [
                "status" => "500",
                "title"  => "testing error string",
            ],
            [
                "code"   => "123",
                "title"  => "testing error string in exception",
                "status" => "500",
            ]
        ], $result['errors'], "Response content mismatch for array of string and integer");
    }

}
