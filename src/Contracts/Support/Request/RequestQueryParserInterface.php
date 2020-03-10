<?php
namespace Czim\JsonApi\Contracts\Support\Request;

interface RequestQueryParserInterface
{
    /**
     * Returns full filter data.
     *
     * @return array
     */
    public function getFilter(): array;

    /**
     * Returns a specific key's value from the filter.
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public function getFilterValue(string $key, $default = null);

    /**
     * Returns raw JSON-API include string.
     *
     * @return string|null
     */
    public function getRawIncludes(): ?string;

    /**
     * Returns JSON-API includes as array of strings.
     *
     * @return string[]
     */
    public function getIncludes(): array;

    /**
     * Returns full page data.
     *
     * @return array
     */
    public function getPageData(): array;

    /**
     * @return int
     */
    public function getPageNumber(): int;

    /**
     * @return int
     */
    public function getPageSize(): int;

    /**
     * @return int
     */
    public function getPageOffset(): int;

    /**
     * @return int
     */
    public function getPageLimit(): int;

    /**
     * @return mixed
     */
    public function getPageCursor();

    /**
     * Returns raw sort string.
     *
     * @return string|null
     */
    public function getRawSort(): ?string;

    /**
     * Returns sort as array of sort strings.
     *
     * This explodes the sort parameter by its delimiter (comma)
     *
     * @return string[]
     */
    public function getSort(): array;
}
