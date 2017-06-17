<?php
namespace Czim\JsonApi\Contracts\Support\Request;

interface RequestQueryParserInterface
{

    /**
     * Returns full filter data.
     *
     * @return array
     */
    public function getFilter();

    /**
     * Returns a specific key's value from the filter.
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public function getFilterValue($key, $default = null);

    /**
     * Returns raw JSON-API include string.
     *
     * @return string
     */
    public function getRawIncludes();

    /**
     * Returns JSON-API includes as array of strings.
     *
     * @return string[]
     */
    public function getIncludes();

    /**
     * Returns full page data.
     *
     * @return array
     */
    public function getPageData();

    /**
     * @return int
     */
    public function getPageNumber();

    /**
     * @return int
     */
    public function getPageSize();

    /**
     * @return int
     */
    public function getPageOffset();

    /**
     * @return int
     */
    public function getPageLimit();

    /**
     * @return mixed
     */
    public function getPageCursor();

    /**
     * Returns raw sort string.
     *
     * @return string|null
     */
    public function getRawSort();

    /**
     * Returns sort as array of sort strings.
     *
     * This explodes the sort parameter by its delimiter (comma)
     *
     * @return string[]
     */
    public function getSort();

}
