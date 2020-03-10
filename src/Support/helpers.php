<?php

use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\AcceptHeader;

if ( ! function_exists('jsonapi_request')) {
    /**
     * Returns JSON-API request instance.
     *
     * This may be used as `request()` would, but adds JSON-API related information.
     *
     * @return \Czim\JsonApi\Http\Requests\JsonApiRequest
     */
    function jsonapi_request(): \Czim\JsonApi\Http\Requests\JsonApiRequest
    {
        return app(\Czim\JsonApi\Http\Requests\JsonApiRequest::class);
    }
}

if ( ! function_exists('jsonapi_request_create')) {
    /**
     * Returns JSON-API request instance for create request.
     *
     * This may be used as `request()` would, but adds JSON-API related information.
     *
     * @return \Czim\JsonApi\Http\Requests\JsonApiCreateRequest
     */
    function jsonapi_request_create(): \Czim\JsonApi\Http\Requests\JsonApiCreateRequest
    {
        return app(\Czim\JsonApi\Http\Requests\JsonApiCreateRequest::class);
    }
}

if ( ! function_exists('jsonapi_query')) {
    /**
     * Returns JSON-API request query parser.
     *
     * @return \Czim\JsonApi\Support\Request\RequestQueryParser
     */
    function jsonapi_query(): \Czim\JsonApi\Support\Request\RequestQueryParser
    {
        return app(\Czim\JsonApi\Support\Request\RequestQueryParser::class);
    }
}

if ( ! function_exists('jsonapi_response')) {
    /**
     * Casts a given array as a JSON-API response.
     *
     * @param  mixed  $data
     * @param  int    $status
     * @param  array  $headers
     * @param  int    $options
     * @return \Czim\JsonApi\Http\Responses\JsonApiResponse
     */
    function jsonapi_response($data = null, int $status = 200, array $headers = [], int $options = 0): \Czim\JsonApi\Http\Responses\JsonApiResponse
    {
        return new \Czim\JsonApi\Http\Responses\JsonApiResponse($data, $status, $headers, $options);
    }
}

if ( ! function_exists('jsonapi_encode')) {
    /**
     * Encodes a JSON-API array with a fresh encoder instance.
     *
     * @param  mixed $data
     * @param  array  $includes
     * @return array
     */
    function jsonapi_encode($data, array $includes = null): array
    {
        /** @var \Czim\JsonApi\Contracts\Encoder\EncoderInterface $encoder */
        $encoder = app(\Czim\JsonApi\Contracts\Encoder\EncoderInterface::class);

        return $encoder->encode($data, $includes);
    }
}

if ( ! function_exists('jsonapi_error')) {
    /**
     * Makes a JSON-API response instance for an error or exception.
     *
     * @param  mixed $data
     * @return \Czim\JsonApi\Http\Responses\JsonApiResponse
     */
    function jsonapi_error($data): \Czim\JsonApi\Http\Responses\JsonApiResponse
    {
        $encoded = jsonapi_encode($data);

        $status = (int) Arr::get($encoded, 'errors.0.status', 500);

        return jsonapi_response($encoded, $status);
    }
}

if ( ! function_exists('is_jsonapi_request')) {
    /**
     * Returns whether the current request is JSON-API.
     *
     * @return bool
     */
    function is_jsonapi_request(): bool
    {
        $acceptHeader = AcceptHeader::fromString(request()->header('accept'));

        return $acceptHeader->has('application/vnd.api+json');
    }
}
