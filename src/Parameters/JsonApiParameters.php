<?php
namespace Czim\JsonApi\Parameters;

use Czim\JsonApi\Contracts\JsonApiParametersInterface;
use Czim\JsonApi\Contracts\SortParameterInterface;
use InvalidArgumentException;

class JsonApiParameters implements JsonApiParametersInterface
{

    /**
     * @var string[]
     */
    protected $includePaths = [];

    /**
     * @var mixed[]
     */
    protected $filter = [];

    /**
     * @var SortParameterInterface[]
     */
    protected $sortParameters = [];


    /**
     * Stores include paths
     *
     * @param string[] $paths
     * @return $this
     */
    public function setIncludePaths(array $paths)
    {
        $this->includePaths = $paths;

        return $this;
    }

    /**
     * Merges new include paths with any previously set
     *
     * @param array $paths
     * @return $this
     */
    public function mergeIncludePaths(array $paths)
    {
        $this->includePaths = array_merge($this->includePaths, $paths);

        return $this;
    }

    /**
     * Returns dot-notation include paths as determined by the current request
     *
     * @return string[]
     */
    public function getIncludePaths()
    {
        return $this->includePaths;
    }

    /**
     * Stores filter data, overwriting any previously set filters
     *
     * @param mixed[] $filter
     * @return $this
     */
    public function setFilter(array $filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Stores specific filter value by key
     *
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    public function setFilterValue($key, $value = null)
    {
        $this->filter[ $key ] = $value;

        return $this;
    }

    /**
     * Returns custom filter data for JSON-API request
     *
     * @return mixed[]
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Returns specific filter value by key
     *
     * @param string $key
     * @return mixed
     */
    public function getFilterValue($key)
    {
        if ( ! array_key_exists($key, $this->filter)) {
            return null;
        }

        return $this->filter[ $key ];
    }

    /**
     * Sets an array of sorting parameters, overwriting any previously set sorting
     *
     * @param SortParameterInterface[] $sortParameters
     * @return $this
     */
    public function setSortParameters(array $sortParameters)
    {
        foreach ($sortParameters as $sortParameter) {
            if ( ! ($sortParameter instanceof SortParameterInterface)) {
                throw new InvalidArgumentException("All SortParameters must implement the SortParameterInface");
            }
        }

        $this->sortParameters = $sortParameters;

        return $this;
    }

    /**
     * Appends one sorting parameter to the current sorting
     *
     * @param SortParameterInterface $parameter
     * @return $this
     */
    public function addSortParameter(SortParameterInterface $parameter)
    {
        $this->sortParameters[] = $parameter;

        return $this;
    }

    /**
     * Returns sorting parameters
     *
     * @return SortParameterInterface[]
     */
    public function getSortParameters()
    {
        return $this->sortParameters;
    }

}
