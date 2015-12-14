<?php
namespace Czim\JsonApi\Contracts;

interface SortParameterInterface
{

    /**
     * @param string $field         identifier for sorting field or column
     * @param string $direction     'asc' or 'desc' (defaults to 'asc')
     */
    public function __construct($field, $direction = 'asc');

    /**
     * Returns the sorting field or column
     *
     * @return string
     */
    public function getField();

    /**
     * Returns the sorting direction
     *
     * @return string   'asc' or 'desc'
     */
    public function getDirection();

    /**
     * Returns string format for field and direction
     * (Perhaps something like 'field' or '-field' for desc)
     *
     * @return string
     */
    public function __toString();

}
