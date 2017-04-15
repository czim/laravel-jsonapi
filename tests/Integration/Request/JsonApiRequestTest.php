<?php
namespace Czim\JsonApi\Test\Integration\Request;

use Czim\JsonApi\Enums\RootType;
use Czim\JsonApi\Test\Helpers\Controllers\RequestTestController;
use Czim\JsonApi\Test\TestCase;

class JsonApiRequestTest extends TestCase
{

    /**
     * {@inheritdoc}
     */
    public function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['router']->any('request', RequestTestController::class . '@request');
        $app['router']->post('create', RequestTestController::class . '@create');
    }

    /**
     * @test
     */
    function it_does_not_validate_get_request_parameters_against_json_schema()
    {
        $this->call(
            'GET',
            'request?page[number]=44'
        );

        $this->assertResponseStatus(200);

        $data = json_decode($this->response->content(), true);

        static::assertEquals(44, $data['query-page-number']);
    }

    /**
     * @test
     */
    function it_parses_query_string_data()
    {
        $this->call(
            'POST',
            'request?page[number]=44',
            $this->getValidRequestData()
        );

        $this->assertResponseStatus(200);

        $data = json_decode($this->response->content(), true);

        static::assertEquals(44, $data['query-page-number']);
    }

    /**
     * @test
     */
    function it_returns_a_422_response_for_invalid_request_data()
    {
        $this->call('POST', 'request', ['test']);

        $this->assertResponseStatus(422);
    }

    /**
     * @test
     */
    function it_parses_valid_request_data()
    {
        $this->call(
            'POST',
            'request',
            $this->getValidRequestData()
        );

        $this->assertResponseStatus(200);

        $data = json_decode($this->response->content(), true);

        static::assertInternalType('array', $data, 'Invalid JSON returned');
        static::assertEquals(RootType::RESOURCE, $data['data-root-type']);
    }

    /**
     * @test
     */
    function it_parses_valid_create_data()
    {
        $this->call(
            'POST',
            'create',
            $this->getValidCreateData()
        );

        $this->assertResponseStatus(200);

        $data = json_decode($this->response->content(), true);

        static::assertInternalType('array', $data, 'Invalid JSON returned');
        static::assertEquals(RootType::RESOURCE, $data['data-root-type']);
    }


    /**
     * @return array
     */
    protected function getValidRequestData()
    {
        return [
            'data' => [
                'id'         => '1',
                'type'       => 'test-posts',
                'attributes' => [
                    'title'   => 'Some Basic Title',
                    'type'    => 'notice',
                    'checked' => true,
                ],
            ],
            'meta' => [
                'test' => 'value',
            ],
            'links' => [
                'self' => 'http://localhost/test',
            ],
        ];
    }

    /**
     * @return array
     */
    protected function getValidCreateData()
    {
        return [
            'data' => [
                'type'       => 'test-posts',
                'attributes' => [
                    'title'   => 'Some Basic Title',
                    'type'    => 'notice',
                    'checked' => true,
                ],
            ],
        ];
    }

}
