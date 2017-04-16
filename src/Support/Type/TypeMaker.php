<?php
namespace Czim\JsonApi\Support\Type;

use Czim\JsonApi\Contracts\Support\Type\TypeMakerInterface;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class TypeMaker implements TypeMakerInterface
{
    const WORD_SEPARATOR      = '-';
    const NAMESPACE_SEPARATOR = '--';

    /**
     * Makes a JSON-API type string for any source content.
     *
     * @param mixed $source
     * @return string
     */
    public function makeFor($source)
    {
        if ($source instanceof Model) {
            return $this->makeForModel($source);
        }

        if (is_object($source)) {
            return str_plural(snake_case(class_basename($source), static::WORD_SEPARATOR));
        }

        if (is_string($source)) {
            return snake_case($source, static::WORD_SEPARATOR);
        }

        throw new InvalidArgumentException("Cannot make type for given source");
    }

    /**
     * Makes a JSON-API type for a given model instance.
     *
     * @param Model       $record
     * @param null|string $offsetNamespace
     * @return string
     */
    public function makeForModel(Model $record, $offsetNamespace = null)
    {
        if (null === $offsetNamespace) {
            $offsetNamespace = config('jsonapi.transform.type.trim-namespace');
        }

        $baseDasherized = str_plural(snake_case(class_basename($record), static::WORD_SEPARATOR));

        if (null !== $offsetNamespace) {

            $namespaceDasherized = $this->dasherizeNamespace(
                $this->trimNamespace(get_class($record), $offsetNamespace, class_basename($record))
            );

            $baseDasherized = ($namespaceDasherized ? $namespaceDasherized . static::NAMESPACE_SEPARATOR : null)
                            . $baseDasherized;
        }

        return $baseDasherized;
    }

    /**
     * Strips the first part of a namespace if it matches offset.
     *
     * @param string $namespace
     * @param string $offset    bit to cut off the start
     * @param string $trail     bit to cut off the end
     * @return string
     */
    protected function trimNamespace($namespace, $offset, $trail)
    {
        if (starts_with($namespace, $offset)) {
            $namespace = substr($namespace, strlen($offset));
        }

        if (ends_with($namespace, $trail)) {
            $namespace = substr($namespace, 0, -1 * strlen($trail));
        }

        $namespace = trim($namespace, '\\');

        return $namespace;
    }

    /**
     * @param string $namespace
     * @return string
     */
    protected function dasherizeNamespace($namespace)
    {
        $parts = explode('\\', $namespace);
        $parts = array_map(
            function ($part) {
                return snake_case($part, static::WORD_SEPARATOR);
            },
            array_filter($parts)
        );

        return implode(static::NAMESPACE_SEPARATOR, $parts);
    }
}
