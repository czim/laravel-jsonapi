<?php
namespace Czim\JsonApi\Requests;

use Czim\JsonApi\Contracts\JsonApiDataAccessorsInterface;
use Czim\JsonApi\DataObjects;
use Czim\JsonApi\Encoding\JsonApiEncoder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Neomerx\JsonApi\Document\Error as JsonApiError;

class JsonApiRequest extends FormRequest implements JsonApiDataAccessorsInterface
{
    use JsonApiDataAccessorsTrait,
        JsonApiDataValidateAndParseTrait;

    /**
     * @var array
     */
    protected $jsonApiContentArray;

    /**
     * @var DataObjects\Main
     */
    protected $jsonApiContent;


    /**
     * @return array
     */
    public function rules()
    {
        return [];
    }

    /**
     * @return bool
     */
    public function authorize()
    {
        return true;
    }


    /**
     * @inheritDocs
     */
    public function validate()
    {
        $this->loadJsonApiContent();
        $this->validateJsonApiContent();
        $this->interpretJsonApiContent();

        parent::validate();
    }

    /**
     * Returns the request
     *
     * For use in traits (for a makeshift template pattern)
     *
     * @return $this
     */
    protected function getRequest()
    {
        return $this;
    }

    /**
     * Loads
     */
    protected function loadJsonApiContent()
    {
        $this->jsonApiContentArray = $this->json()->all();
    }

    /**
     * Determine if the request is sending JSON.
     * Overridden to ensure that JSON API content is correctly considered JSON.
     *
     * @return bool
     */
    public function isJson()
    {
        return parent::isJson() || ($this->header('CONTENT_TYPE') === 'application/vnd.api+json');
    }

    /**
     * Returns an error JsonResponse on invalid request
     *
     * @param string[]|JsonApiError[] $errors
     * @return JsonResponse
     */
    public function response(array $errors)
    {
        $encoder = app(JsonApiEncoder::class, [ app(), null ]);

        return $encoder->errors($errors, 422);
    }

}
