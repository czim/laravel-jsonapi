<?php
namespace Czim\JsonApi\Contracts\Support\Type;

use Illuminate\Database\Eloquent\Model;

interface TypeMakerInterface
{

    /**
     * Makes a JSON-API type string for any source content.
     *
     * @param mixed $source
     * @return string
     */
    public function makeFor($source);

    /**
     * Makes a JSON-API type for a given model instance.
     *
     * @param Model       $record
     * @param null|string $offsetNamespace
     * @return string
     */
    public function makeForModel(Model $record, $offsetNamespace = null);

}
