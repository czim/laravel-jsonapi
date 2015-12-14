<?php
namespace Czim\JsonApi\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\Translation\TranslatorInterface;

class JsonApiRequest extends FormRequest
{
    /**
     * @var
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
     * Validation rules for general JSON-API structure and content.
     *
     * @return array
     */
    public function jsonApiRules()
    {
        $rules = [
            'errors'   => 'array|required_without_all:data,meta',
            'meta'     => 'array|jsonapi_meta|required_without_all:data,errors',
            'included' => 'array',
            'links'    => 'array|jsonapi_links',
            'jsonapi'  => 'array|jsonapi_jsonapi',
        ];

        // data validation depends on whether it is a list of resources or a single resource
        if ( ! isset($this->jsonApiContent['data']) || ! is_array($this->jsonApiContent['data'])) {

            $rules['data'] = 'array|required_without_all:meta,errors';

        } else {

            $keys = array_keys($this->jsonApiContent['data']);

            $resourceRule = (strtolower($this->method()) === 'post')
                          ?   'jsonapi_resource_create'
                          :   'jsonapi_resource';

            if (    count($keys)
                &&  count($keys) === count(array_filter($keys, function ($key) { return is_numeric($key); }))
            ) {
                foreach ($keys as $key) {
                    $rules[ 'data.' . $key ] = 'array|' . $resourceRule;
                }
            } else {
                $rules['data'] = 'array|' . $resourceRule;
            }
        }

        // included is a non-associative array with items that should each be a resource
        if (isset($this->jsonApiContent['included']) && is_array($this->jsonApiContent['included'])) {

            foreach (array_keys($this->jsonApiContent['included']) as $key) {
                $rules[ 'included.' . $key ] = 'array|jsonapi_resource';
            }
        }

        // errors is a non-associative array with items that should each be an error object
        if (isset($this->jsonApiContent['errors']) && is_array($this->jsonApiContent['errors'])) {

            foreach (array_keys($this->jsonApiContent['errors']) as $key) {
                $rules[ 'errors.' . $key ] = 'array|jsonapi_error';
            }
        }

        return $rules;
    }

    /**
     * @inheritDocs
     */
    public function validate()
    {
        $this->loadJsonApiContent();
        $this->validateJsonApiContent();

        parent::validate();
    }

    /**
     * Loads
     */
    protected function loadJsonApiContent()
    {
        $this->jsonApiContent = $this->json()->all();
    }


    /**
     * Validates the request's content as valid JSON-API content
     */
    protected function validateJsonApiContent()
    {
        // check whether we may have content that is not valid json
        // and throw an exception if we do
        if ( ! $this->hasEmptyJsonContent() && empty($this->jsonApiContent)) {

            throw new HttpResponseException($this->response([
                'Request content is not valid JSON'
            ]));
        }

        // check if we have anything to validate (no content is fine)
        if (empty($this->jsonApiContent)) return;


        $validator = app(
            JsonApiValidator::class,
            [ app(TranslatorInterface::class), $this->jsonApiContent, $this->jsonApiRules() ]
        );

        if ( ! $validator->passes()) {
            $this->failedValidation($validator);
        }
    }

    /**
     * Returns whether the body content is empty (as json data)
     *
     * @return bool
     */
    protected function hasEmptyJsonContent()
    {
        if (empty(trim($this->content))) return true;

        return (bool) preg_match('#^\s*(\[\s*\]|\{\s*\})\s*&#i', $this->content);
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
     * @param array $errors
     * @return JsonResponse
     *
     * @todo replace this with proper neomerx JsonApi error response...
     */
    public function response(array $errors)
    {
        return new JsonResponse([
            'errors' => [
                [
                    'title' => json_encode($errors),
                ],
            ],
        ], 422);
    }

}
