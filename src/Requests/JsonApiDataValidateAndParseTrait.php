<?php
namespace Czim\JsonApi\Requests;

use Czim\JsonApi\DataObjects;
use Illuminate\Http\Exception\HttpResponseException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * To use this, make sure there is a (protected) property $jsonApiContentArray
 * with an associative array (only, no objects) representation of JSON-API data.
 *
 * Additionally there must be a (protected) method getRequest() that returns the
 * current/relevant Illuminate Request.
 */
trait JsonApiDataValidateAndParseTrait
{

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

            $resourceRule = (strtolower($this->getRequest()->method()) === 'post')
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
     * Validates the request's content as valid JSON-API content
     */
    protected function validateJsonApiContent()
    {
        // check whether we may have content that is not valid json
        // and throw an exception if we do
        if ( ! $this->hasEmptyJsonContent() && empty($this->jsonApiContentArray)) {

            throw new HttpResponseException(
                $this->getRequest()->response([
                    'Request content is not valid JSON'
                ])
            );
        }

        // check if we have anything to validate (no content is fine)
        if (empty($this->jsonApiContentArray)) return;


        $validator = app(
            JsonApiValidator::class,
            [ app(TranslatorInterface::class), $this->jsonApiContentArray, $this->jsonApiRules() ]
        );

        if ( ! $validator->passes()) {
            $this->getRequest()->failedValidation($validator);
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
        if (empty(trim($this->getRequest()->content))) return true;

        return (bool) preg_match('#^\s*(\[\s*\]|\{\s*\})\s*&#i', $this->getRequest()->content);
    }

}
