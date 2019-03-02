<?php
namespace Czim\JsonApi\Support\Type;

use Czim\JsonApi\Contracts\Support\Type\TypeMakerInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
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
            return $this->pluralizeIfConfiguredTo(
                Str::snake(class_basename($source), static::WORD_SEPARATOR)
            );
        }

        if (is_string($source)) {
            return Str::snake($source, static::WORD_SEPARATOR);
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

        $baseDasherized = $this->pluralizeIfConfiguredTo(
            Str::snake(class_basename($record), static::WORD_SEPARATOR)
        );

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
        if (Str::startsWith($namespace, $offset)) {
            $namespace = substr($namespace, strlen($offset));
        }

        if (Str::endsWith($namespace, $trail)) {
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
                return Str::snake($part, static::WORD_SEPARATOR);
            },
            array_filter($parts)
        );

        return implode(static::NAMESPACE_SEPARATOR, $parts);
    }

    /**
     * @param string $type
     * @return string
     */
    protected function pluralizeIfConfiguredTo($type)
    {
        if ($this->plural()) {
            return Str::plural($type);
        }

        return $type;
    }

    /**
     * Returns whether JSON-API type must be plural.
     *
     * @return bool
     */
    protected function plural()
    {
        return (bool) config('jsonapi.type.plural', true);
    }
}
