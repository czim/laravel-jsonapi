<?php
namespace Czim\JsonApi\Support\Type;

use Czim\JsonApi\Contracts\Support\Type\TypeMakerInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use InvalidArgumentException;

class TypeMaker implements TypeMakerInterface
{
    public const WORD_SEPARATOR      = '-';
    public const NAMESPACE_SEPARATOR = '--';

    /**
     * Makes a JSON-API type string for any source content.
     *
     * @param mixed $source
     * @return string
     */
    public function makeFor($source): string
    {
        if ($source instanceof Model) {
            return $this->makeForModelClass(get_class($source));
        }

        if (is_a($source, Model::class, true)) {
            return $this->makeForModelClass($source);
        }

        if (is_object($source)) {
            return $this->pluralizeIfConfiguredTo(
                Str::snake(class_basename($source), static::WORD_SEPARATOR)
            );
        }

        if (is_string($source)) {
            return Str::snake($source, static::WORD_SEPARATOR);
        }

        throw new InvalidArgumentException('Cannot make type for given source');
    }

    /**
     * Makes a JSON-API type for a given model FQN.
     *
     * @param string      $class
     * @param null|string $offsetNamespace
     * @return string
     */
    public function makeForModelClass(string $class, ?string $offsetNamespace = null): string
    {
        if (null === $offsetNamespace) {
            $offsetNamespace = config('jsonapi.transform.type.trim-namespace');
        }

        $baseDasherized = $this->pluralizeIfConfiguredTo(
            Str::snake(class_basename($class), static::WORD_SEPARATOR)
        );

        if (null !== $offsetNamespace) {

            $namespaceDasherized = $this->dasherizeNamespace(
                $this->trimNamespace($class, $offsetNamespace, class_basename($class))
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
    protected function trimNamespace(string $namespace, string $offset, string $trail): string
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

    protected function dasherizeNamespace(string $namespace): string
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

    protected function pluralizeIfConfiguredTo(string $type): string
    {
        if ($this->shouldBePlural()) {
            return Str::plural($type);
        }

        return $type;
    }

    protected function shouldBePlural(): bool
    {
        return (bool) config('jsonapi.type.plural', true);
    }
}
