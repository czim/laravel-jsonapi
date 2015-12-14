<?php
namespace Czim\JsonApi\Parameters;

use Czim\JsonApi\Contracts\SortParameterInterface;
use InvalidArgumentException;

class SortParameter implements SortParameterInterface
{

    /**
     * @var string
     */
    protected $field;

    /**
     * @var string  'asc' or 'desc'
     */
    protected $direction;


    /**
     * @param string $field
     * @param string $direction
     */
    public function __construct($field, $direction = 'asc')
    {
        $direction = strtolower($direction);

        if ($direction !== 'asc' && $direction !== 'desc') {
            throw new InvalidArgumentException("Direction must be either 'asc' or 'desc'");
        }

        $this->field     = $field;
        $this->direction = $direction;
    }


    /**
     * Returns the sorting field or column
     *
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Returns the sorting direction
     *
     * @return string   'asc' or 'desc'
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * Returns string format for field and direction
     * (Perhaps something like 'field' or '-field' for desc)
     *
     * @return string
     */
    public function __toString()
    {
        return ($this->direction === 'desc' ? '-' : null) . $this->field;
    }

}
