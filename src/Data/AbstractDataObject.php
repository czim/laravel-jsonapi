<?php
namespace Czim\JsonApi\Data;

use Czim\DataObject\AbstractDataObject as CzimAbstractDataObject;
use Czim\DataObject\Contracts\DataObjectInterface;
use Illuminate\Contracts\Support\Arrayable;
use UnexpectedValueException;

abstract class AbstractDataObject extends CzimAbstractDataObject
{

    /**
     * Attributes to convert to objects when accessed
     *
     * This is a way to make nested objects on the fly, without parsing the whole
     * tree beforehand. Simply add the attribute name and the data object class to
     * decorate it as:
     *
     *     'brand' => Brand::class,
     *
     * If the attribute is an array of arrays that should be decorated as data objects,
     * i.e. when it is a list of related items, you can set it to make individual
     * data objects for each item:
     *
     *     'brand' => Brand::class . '[]',
     *
     * @var array
     */
    protected $objects = [];


    /**
     * Converts attributes to specific dataobjects if configured to
     *
     * @param string $key
     * @return mixed|DataObjectInterface
     */
    public function &getAttributeValue($key)
    {
        if (    ! count($this->objects)
            ||  ! array_key_exists($key, $this->objects)
        ) {
            return parent::getAttributeValue($key);
        }

        $dataObjectClass = $this->objects[$key];
        $dataObjectArray = false;
        $dataObjectForce = false;

        // Following an object class with ! enforces the indicated object, even if the key is unset.
        // This only works for singular objects (without [])
        if (substr($dataObjectClass, -1) === '!') {
            $dataObjectClass = substr($dataObjectClass, 0, -1);
            $dataObjectForce = true;
        }

        if ( ! isset($this->attributes[$key])) {

            if ($dataObjectForce) {
                $this->attributes[$key] = new $dataObjectClass;
                return $this->attributes[$key];
            }

            $null = null;
            return $null;
        }

        // Following an object class with [] interprets it as an array of instances
        if (substr($dataObjectClass, -2) === '[]') {
            $dataObjectClass = substr($dataObjectClass, 0, -2);
            $dataObjectArray = true;
        }

        if ($dataObjectArray) {

            if (is_array($this->attributes[$key])) {

                foreach ($this->attributes[$key] as $index => &$item) {

                    if (null === $item) {
                        continue;
                    }

                    if ( ! is_a($item, $dataObjectClass)) {

                        $item = $this->makeNestedDataObject($dataObjectClass, $item, $key . '.' . $index);
                    }
                }
            }

            unset($item);

        } else {

            if ( ! is_a($this->attributes[ $key ], $dataObjectClass)) {

                $this->attributes[ $key ] = $this->makeNestedDataObject($dataObjectClass, $this->attributes[ $key ], $key);
            }
        }

        return $this->attributes[$key];
    }

    /**
     * @param string $class
     * @param mixed  $data
     * @param string $key
     * @return mixed
     */
    protected function makeNestedDataObject($class, $data, $key)
    {
        $data = ($data instanceof Arrayable) ? $data->toArray() : $data;

        if ( ! is_array($data)) {
            throw new UnexpectedValueException(
                "Cannot instantiate data object '{$class}' with non-array data for key '{$key}'"
                . (is_scalar($data) || is_object($data) && method_exists($data, '__toString')
                    ?   ' (data: ' . (string) $data . ')'
                    :   null)
            );
        }

        /** @var DataObjectInterface $data */
        return new $class($data);
    }

    /**
     * Get the value for a given offset.
     *
     * @param mixed $offset
     * @return mixed
     * @codeCoverageIgnore
     */
    public function offsetGet($offset)
    {
        // let it behave like the magic getter, return null if it doesn't exist
        if ( ! $this->offsetExists($offset)) return null;

        return $this->getAttribute($offset);
    }

}
