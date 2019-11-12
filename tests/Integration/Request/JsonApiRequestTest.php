<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\JsonApi\Test\Integration\Request;

use Czim\JsonApi\Enums\RootType;
use Czim\JsonApi\Test\Helpers\Controllers\RequestTestController;
use Czim\JsonApi\Test\TestCase;

class JsonApiRequestTest extends TestCase
{

    /**
     * {@inheritdoc}
     */
    public function getEnvironmentSetUp($app): void
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
        $response = $this->json(
            'GET',
            'request?page[number]=44'
        );

        $response->assertStatus(200);

        $data = json_decode($response->content());

        static::assertEquals(44, $data->{'query-page-number'});
    }

    /**
     * @test
     */
    function it_parses_query_string_data()
    {
        $response = $this->json(
            'POST',
            'request?page[number]=44',
            $this->getValidRequestData()
        );

        $response->assertStatus(200);

        $data = json_decode($response->content());

        static::assertEquals(44, $data->{'query-page-number'});
    }

    /**
     * @test
     */
    function it_returns_a_422_response_for_invalid_request_data()
    {
        $response = $this->call('POST', 'request', ['test']);

        $response->assertStatus(422);
    }

    /**
     * @test
     */
    function it_parses_valid_request_data()
    {
        $response = $this->json(
            'POST',
            'request',
            $this->getValidRequestData()
        );

        $response->assertStatus(200);

        $data = json_decode($response->content());

        static::assertIsObject($data, 'Invalid JSON returned');
        static::assertEquals(RootType::RESOURCE, $data->{'data-root-type'});
    }

    /**
     * @test
     */
    function it_parses_valid_create_data()
    {
        $response = $this->call(
            'POST',
            'create',
            $this->getValidCreateData()
        );

        $response->assertStatus(200);

        $data = json_decode($response->content());

        static::assertIsObject($data, 'Invalid JSON returned');
        static::assertEquals(RootType::RESOURCE, $data->{'data-root-type'});
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
