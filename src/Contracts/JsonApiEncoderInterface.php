<?php
namespace Czim\JsonApi\Contracts;

use Illuminate\Contracts\Support\Arrayable;

interface JsonApiEncoderInterface
{
    /**
     * Encodes data as valid JSON API response and returns it
     *
     * @param mixed $data
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function response($data);

    /**
     * Encodes data as valid JSON API output
     *
     * @param mixed $data
     * @param bool  $resetMeta if true, clears the meta data after encoding
     * @return $this
     */
    public function encode($data, $resetMeta = true);

    /**
     * Encodes errors as JSON-API error response
     *
     * @param array|Arrayable $errors
     * @param int             $status HTTP status code for response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function errors($errors, $status = 500);

    /**
     * Checks whether a relation's data should always be included
     *
     * @param mixed $relation
     * @return bool
     */
    public function alwaysIncludeDataForRelation($relation);

    /**
     * Returns the JSON-API Parameters singleton
     *
     * @return JsonApiParametersInterface
     */
    public function getParameters();

    /**
     * Returns the JSON-API Meta singleton
     *
     * @return JsonApiCurrentMetaInterface
     */
    public function getMeta();

    /**
     * Clears all JSON-API Meta data
     *
     * @return $this
     */
    public function clearMeta();

}
