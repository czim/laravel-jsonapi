<?php
namespace Czim\JsonApi\Requests;

use Czim\JsonApi\DataObjects;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\Translation\TranslatorInterface;

class JsonApiRequest extends FormRequest
{
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
     * Validation rules for general JSON-API structure and content.
     *
     * @return array
     */
    public function jsonApiRules()
    {
        $rules = [
            'errors'   => 'array|required_without_all:data,meta',
            'included' => 'array',
            'jsonapi'  => 'array|jsonapi_jsonapi',
            'links'    => 'array|jsonapi_links',
            'meta'     => 'array|jsonapi_meta|required_without_all:data,errors',
        ];

        // data validation depends on whether it is a list of resources or a single resource
        if ( ! isset($this->jsonApiContentArray['data']) || ! is_array($this->jsonApiContentArray['data'])) {

            $rules['data'] = 'array|required_without_all:meta,errors';

        } else {

            $keys = array_keys($this->jsonApiContentArray['data']);

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
        if (isset($this->jsonApiContentArray['included']) && is_array($this->jsonApiContentArray['included'])) {

            foreach (array_keys($this->jsonApiContentArray['included']) as $key) {
                $rules[ 'included.' . $key ] = 'array|jsonapi_resource';
            }
        }

        // errors is a non-associative array with items that should each be an error object
        if (isset($this->jsonApiContentArray['errors']) && is_array($this->jsonApiContentArray['errors'])) {

            foreach (array_keys($this->jsonApiContentArray['errors']) as $key) {
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
        $this->interpretJsonApiContent();

        parent::validate();
    }

    /**
     * Loads
     */
    protected function loadJsonApiContent()
    {
        $this->jsonApiContentArray = $this->json()->all();
    }

    /**
     * Validates the request's content as valid JSON-API content
     */
    protected function validateJsonApiContent()
    {
        // check whether we may have content that is not valid json
        // and throw an exception if we do
        if ( ! $this->hasEmptyJsonContent() && empty($this->jsonApiContentArray)) {

            throw new HttpResponseException($this->response([
                'Request content is not valid JSON'
            ]));
        }

        // check if we have anything to validate (no content is fine)
        if (empty($this->jsonApiContentArray)) return;


        $validator = app(
            JsonApiValidator::class,
            [ app(TranslatorInterface::class), $this->jsonApiContentArray, $this->jsonApiRules() ]
        );

        if ( ! $validator->passes()) {
            $this->failedValidation($validator);
        }
    }

    /**
     * Interprets the jsonApiContent and converts it to dataobject tree
     */
    protected function interpretJsonApiContent()
    {
        $this->jsonApiContent = app(DataObjects\Main::class, [ $this->jsonApiContentArray ]);

        unset( $this->jsonApiContentArray );
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


    // ------------------------------------------------------------------------------
    //      Accessors for JSON-API content
    // ------------------------------------------------------------------------------

    /**
     * Returns main data from the json api object
     *
     * @return Resource|Resource[]
     */
    protected function getJsonApiData()
    {
        return $this->jsonApiContent->data;
    }

    /**
     * Returns whether the main JSON-API data contains a single resource
     * (instead of several)
     *
     * @return bool
     */
    public function isSingleResource()
    {
        return ($this->getJsonApiData() instanceof DataObjects\Resource);
    }

    /**
     * Returns data for resource, by index if multiple
     *
     * @param int $index
     * @return DataObjects\Resource|null
     */
    public function getResource($index = 0)
    {
        if (is_null($this->jsonApiContent->data)) return null;

        if ($this->isSingleResource()) {
            return $this->getJsonApiData();
        }

        $index = (int) $index;

        if ( ! isset($this->jsonApiContent['data'][ $index ])) {
            throw new \InvalidArgumentException("JSON-API Data Resource with index $index is not set");
        }

        return $this->jsonApiContent['data'][ $index ];
    }

    /**
     * Returns top-level resource type
     *
     * @param int $index    index of the resource if not single-resource data
     * @return null|string
     */
    public function getType($index = 0)
    {
        if (is_null($this->jsonApiContent->data)) return null;

        if ($this->isSingleResource()) {
            return $this->jsonApiContent->data['type'];
        }

        $index = (int) $index;

        if ( ! isset($this->jsonApiContent['data'][ $index ])) {
            throw new \InvalidArgumentException("JSON-API Data Resource with index $index is not set");
        }

        return $this->jsonApiContent['data'][ $index ]['type'];
    }

    /**
     * Returns top level resource ID
     *
     * @param int $index    index of the resource if not single-resource data
     * @return string|null
     */
    public function getId($index = 0)
    {
        if ($this->isSingleResource()) {
            return $this->jsonApiContent->data['id'];
        }

        $index = (int) $index;

        if ( ! isset($this->jsonApiContent['data'][ $index ])) {
            throw new \InvalidArgumentException("JSON-API Data Resource with index $index is not set");
        }

        return $this->jsonApiContent['data'][ $index ]['id'];
    }

    /**
     * Returns top level relationships
     *
     * @param int $index    index of the resource if not single-resource data
     * @return DataObjects\Relationship[]
     */
    public function getRelationships($index = 0)
    {
        if ($this->isSingleResource()) {

            return $this->jsonApiContent->data['relationships'] ?: [];
        }

        $index = (int) $index;

        if ( ! isset($this->jsonApiContent['data'][ $index ])) {
            throw new \InvalidArgumentException("JSON-API Data Resource with index $index is not set");
        }

        return $this->jsonApiContent['data'][ $index ]['relationships'] ?: [];
    }

    /**
     * Returns attributes from the data object of the json api object
     *
     * @param int $index    index of the resource if not single-resource data
     * @return DataObjects\Attributes|null
     */
    public function getAttributes($index = 0)
    {
        if ($this->isSingleResource()) {

            return $this->jsonApiContent->data['attributes'] ?: null;
        }

        $index = (int) $index;

        if ( ! isset($this->jsonApiContent['data'][ $index ])) {
            throw new \InvalidArgumentException("JSON-API Data Resource with index $index is not set");
        }

        return $this->jsonApiContent['data'][ $index ]['attributes'] ?: null;
    }

}
