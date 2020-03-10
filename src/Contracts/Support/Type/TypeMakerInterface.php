<?php
namespace Czim\JsonApi\Contracts\Support\Type;

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
     * Makes a JSON-API type for a given model FQN.
     *
     * @param string      $class
     * @param null|string $offsetNamespace
     * @return string
     */
    public function makeForModelClass(string $class, ?string $offsetNamespace = null): string;
}
