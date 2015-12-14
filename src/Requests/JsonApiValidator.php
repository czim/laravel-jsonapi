<?php
namespace Czim\JsonApi\Requests;

use Illuminate\Validation\Validator;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Validator extension specifically for validating basic JSON-API
 * request structure.
 */
class JsonApiValidator extends Validator
{

    /**
     * Meta section
     *
     * @param string $attribute
     * @param mixed  $value
     * @param mixed  $parameters
     * @return bool
     */
    public function validateJsonapiMeta($attribute, $value, $parameters)
    {
        // no special rules
        return true;
    }

    /**
     * Resource section
     *
     * @param string $attribute
     * @param mixed  $value
     * @param mixed  $parameters
     * @return bool
     */
    public function validateJsonapiResource($attribute, $value, $parameters)
    {
        return $this->validateNested($attribute, $value, [
            'type'          => 'required|string',
            'id'            => 'required|string',
            'attributes'    => 'array|jsonapi_attributes',
            'relationships' => 'array|jsonapi_relationships',
            'links'         => 'array|jsonapi_links',
            'meta'          => 'array|jsonapi_meta',
        ]);
    }

    /**
     * Resource section when creating new resource
     *
     * @param string $attribute
     * @param mixed  $value
     * @param mixed  $parameters
     * @return bool
     */
    public function validateJsonapiResourceCreate($attribute, $value, $parameters)
    {
        return $this->validateNested($attribute, $value, [
            'type'          => 'required|string',
            'id'            => 'string',
            'attributes'    => 'array|jsonapi_attributes',
            'relationships' => 'array|jsonapi_relationships',
            'links'         => 'array|jsonapi_links',
            'meta'          => 'array|jsonapi_meta',
        ]);
    }

    /**
     * Attributes section
     *
     * @param string $attribute
     * @param mixed  $value
     * @param mixed  $parameters
     * @return bool
     */
    public function validateJsonapiAttributes($attribute, $value, $parameters)
    {
        // no special rules
        // attributes should have their own validation rules in extended formrequests
        return true;
    }

    /**
     * Relationships section
     *
     * @param string $attribute
     * @param mixed  $value
     * @param mixed  $parameters
     * @return bool
     */
    public function validateJsonapiRelationships($attribute, $value, $parameters)
    {
        if ( ! is_array($value) || ! count($value)) return true;

        $rules = [];

        foreach ($value as $relationKey => $relationship) {

            $rules[ $relationKey . '.data' ]  = 'required|array';
            $rules[ $relationKey . '.links' ] = 'array|jsonapi_links';
            $rules[ $relationKey . '.meta' ]  = 'array|jsonapi_meta';

            // relationships should be an array of objects that are
            // either valid definitions of to-one or to-many
            if (isset($relationship['data']) && is_array($relationship['data'])) {

                $keys = array_keys($relationship['data']);

                if (count($keys) && $this->areKeysAllNumeric($keys)) {
                    // to-many
                    foreach ($keys as $key) {
                        $rules[ $relationKey . '.data' . $key . '.type' ] = 'required|string';
                        $rules[ $relationKey . '.data' . $key . '.id' ]   = 'required|string';
                    }

                } else {
                    // to-one
                    if ( ! empty($relationship['data'])) {
                        $rules[ $relationKey . '.data.type' ] = 'required|string';
                        $rules[ $relationKey . '.data.id' ]   = 'required|string';
                    }
                }
            }
        }

        return $this->validateNested($attribute, $value, $rules);
    }

    /**
     * Links section
     *
     * @param string $attribute
     * @param mixed  $value
     * @param mixed  $parameters
     * @return bool
     */
    public function validateJsonapiLinks($attribute, $value, $parameters)
    {
        $rules = [
            'self'  => 'string|url',
            'first' => 'string|url',
            'last'  => 'string|url',
            'prev'  => 'string|url',
            'next'  => 'string|url',
        ];

        if (isset($value['related']) && is_array($value['related'])) {
            $rules['related'] = 'jsonapi_link';
        } else {
            $rules['related'] = 'string|url';
        }

        return $this->validateNested($attribute, $value, $rules);
    }

    /**
     * Link object section
     *
     * @param string $attribute
     * @param mixed  $value
     * @param mixed  $parameters
     * @return bool
     */
    public function validateJsonapiLink($attribute, $value, $parameters)
    {
        return $this->validateNested($attribute, $value, [
            'href' => 'required|string|url',
            'meta' => 'array|jsonapi_meta',
        ]);
    }

    /**
     * JsonApi section
     *
     * @param string $attribute
     * @param mixed  $value
     * @param mixed  $parameters
     * @return bool
     */
    public function validateJsonapiJsonapi($attribute, $value, $parameters)
    {
        return $this->validateNested($attribute, $value, [
            'version' => 'string',
            'meta'    => 'array|jsonapi_meta',
        ]);
    }

    /**
     * Error object section
     *
     * @param string $attribute
     * @param mixed  $value
     * @param mixed  $parameters
     * @return bool
     */
    public function validateJsonapiError($attribute, $value, $parameters)
    {
        return $this->validateNested($attribute, $value, [
            'id'               => 'string',
            'links'            => 'array|jsonapi_links',
            'status'           => 'string',
            'code'             => 'string',
            'title'            => 'string',
            'source'           => 'array',
            'source.pointer'   => 'string',
            'source.parameter' => 'string',
            'meta'             => 'array|jsonapi_meta',
        ]);
    }


    /**
     * Applies nested validation
     *
     * @param string $attributeName
     * @param array  $value
     * @param array  $rules
     * @return bool
     */
    protected function validateNested($attributeName, array $value, array $rules)
    {
        $validator = app(
            JsonApiValidator::class,
            [ app(TranslatorInterface::class), $value, $rules ]
        );

        if ($validator->passes()) return true;

        foreach (array_reverse($validator->failed()) as $attribute => $rules) {

            foreach (array_reverse($rules) as $rule => $parameters) {

                $this->addFailure($attributeName . '.' . $attribute, $rule, $parameters);
            }
        }

        return false;
    }

    /**
     * Returns whether all provided keys are numeric
     *
     * @param array $keys
     * @return bool
     */
    protected function areKeysAllNumeric(array $keys)
    {
        return count($keys) === count(array_filter($keys, function ($key) { return is_numeric($key); }));
    }
}
