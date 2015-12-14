<?php
namespace Czim\JsonApi\Contracts;

interface JsonApiParametersInterface
{

    /**
     * Stores include paths
     *
     * @param string[] $paths
     * @return $this
     */
    public function setIncludePaths(array $paths);

    /**
     * Merges new include paths with any previously set
     *
     * @param array $paths
     * @return $this
     */
    public function mergeIncludePaths(array $paths);

    /**
     * Returns dot-notation include paths as determined by the current request
     *
     * @return string[]
     */
    public function getIncludePaths();


    /**
     * Stores filter data, overwriting any previously set filters
     *
     * @param mixed[] $filter
     * @return $this
     */
    public function setFilter(array $filter);

    /**
     * Stores specific filter value by key
     *
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    public function setFilterValue($key, $value = null);

    /**
     * Returns custom filter data for JSON-API request
     *
     * @return mixed[]
     */
    public function getFilter();

    /**
     * Returns specific filter value by key
     *
     * @param string $key
     * @return mixed
     */
    public function getFilterValue($key);

    /**
     * Sets an array of sorting parameters, overwriting any previously set sorting
     *
     * @param SortParameterInterface[] $sortParameters
     * @return $this
     */
    public function setSortParameters(array $sortParameters);

    /**
     * Appends one sorting parameter to the current sorting
     *
     * @param SortParameterInterface $parameter
     * @return $this
     */
    public function addSortParameter(SortParameterInterface $parameter);

    /**
     * Returns sorting parameters
     *
     * @return SortParameterInterface[]
     */
    public function getSortParameters();

}
