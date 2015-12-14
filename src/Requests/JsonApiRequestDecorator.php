<?php
namespace Czim\JsonApi\Requests;

use Czim\JsonApi\Contracts\JsonApiDataAccessorsInterface;
use Czim\JsonApi\DataObjects;
use Illuminate\Http\Request;

/**
 * Decorator to wrap around a normal Illuminate HTTP Request
 * in order to access JSON-API data in it.
 */
class JsonApiRequestDecorator implements JsonApiDataAccessorsInterface
{
    use JsonApiDataAccessorsTrait,
        JsonApiDataValidateAndParseTrait;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var array
     */
    protected $jsonApiContentArray;

    /**
     * @var DataObjects\Main
     */
    protected $jsonApiContent;


    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Returns the request
     *
     * @return $this
     */
    protected function getRequest()
    {
        return $this->request;
    }

    /**
     * Determine if the request is sending JSON.
     * Overridden to ensure that JSON API content is correctly considered JSON.
     *
     * @return bool
     */
    public function isJson()
    {
        return $this->request->isJson() || ($this->request->header('CONTENT_TYPE') === 'application/vnd.api+json');
    }

    /**
     * Loads
     */
    protected function loadJsonApiContent()
    {
        $this->jsonApiContentArray = $this->request->json()->all();
    }

    /**
     * @inheritDocs
     */
    public function validate()
    {
        $this->loadJsonApiContent();
        $this->validateJsonApiContent();
        $this->interpretJsonApiContent();
    }

}
