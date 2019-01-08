<?php
namespace Czim\JsonApi\Support\Request;

use Czim\JsonApi\Contracts\Support\Request\RequestQueryParserInterface;
use Czim\JsonApi\Exceptions\JsonApiQueryStringValidationException;
use Czim\JsonApi\Exceptions\JsonApiValidationException;
use Illuminate\Http\Request;
use Validator;

/**
 * Class RequestQueryParser
 *
 * Parses contextual data set in the query string (filters, includes, sorting, etc).
 */
class RequestQueryParser implements RequestQueryParserInterface
{
    const DEFAULT_INCLUDE_SEPARATOR = ',';
    const DEFAULT_SORT_SEPARATOR    = ',';

    /**
     * @var Request
     */
    protected $request;

    /**
     * Whether the request has been analyzed yet.
     *
     * @var bool
     */
    protected $analyzed = false;

    /**
     * Filter JSON-API data.
     *
     * @var array
     */
    protected $filter = [];

    /**
     * The raw JSON-API include string.
     *
     * @var string
     */
    protected $include;

    /**
     * Page JSON-API data.
     *
     * @var array
     */
    protected $page = [];

    /**
     * The raw JSON-API sort string.
     *
     * @var string|null
     */
    protected $sort;

    /**
     * Separator token for include strings.
     *
     * @var string
     */
    protected $includeSeparator;

    /**
     * Separator token for sort strings.
     *
     * @var string
     */
    protected $sortSeparator;


    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;

        $this->includeSeparator = config('jsonapi.request.include.separator', self::DEFAULT_INCLUDE_SEPARATOR);
        $this->sortSeparator    = config('jsonapi.request.sort.separator', self::DEFAULT_SORT_SEPARATOR);
    }


    /**
     * Returns full filter data.
     *
     * @return array
     */
    public function getFilter()
    {
        $this->analyze();

        return $this->filter;
    }

    /**
     * Returns a specific key's value from the filter.
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public function getFilterValue($key, $default = null)
    {
        return array_get($this->getFilter(), $key, $default);
    }

    /**
     * Returns raw JSON-API include string.
     *
     * @return string
     */
    public function getRawIncludes()
    {
        $this->analyze();

        return $this->include;
    }

    /**
     * Returns JSON-API includes as array of strings.
     *
     * @return string[]
     */
    public function getIncludes()
    {
        $includeString = $this->getRawIncludes();

        if (null === $includeString) {
            return [];
        }

        $includes = explode($this->includeSeparator, $includeString);

        return array_map('trim', $includes);
    }

    /**
     * Returns full page data.
     *
     * @return array
     */
    public function getPageData()
    {
        $this->analyze();

        return $this->page;
    }

    /**
     * @return int
     */
    public function getPageNumber()
    {
        return (int) array_get($this->getPageData(), 'number', 1);
    }

    /**
     * @return int
     */
    public function getPageSize()
    {
        return (int) array_get($this->getPageData(), 'size');
    }

    /**
     * @return int
     */
    public function getPageOffset()
    {
        return (int) array_get($this->getPageData(), 'offset', 0);
    }

    /**
     * @return int
     */
    public function getPageLimit()
    {
        return (int) array_get($this->getPageData(), 'limit');
    }

    /**
     * @return mixed
     */
    public function getPageCursor()
    {
        return (int) array_get($this->getPageData(), 'cursor');
    }

    /**
     * Returns raw sort string.
     *
     * @return string|null
     */
    public function getRawSort()
    {
        $this->analyze();

        return $this->sort;
    }

    /**
     * Returns sort as array of sort strings.
     *
     * This explodes the sort parameter by its delimiter (comma)
     *
     * @return string[]
     */
    public function getSort()
    {
        $sortString = $this->getRawSort();

        if (null === $sortString) {
            return [];
        }

        $sorts = explode($this->sortSeparator, $sortString);

        return array_map('trim', $sorts);
    }



    /**
     * Analyzes the request to retrieve JSON-API relevant data.
     *
     * @param bool $force
     */
    protected function analyze($force = false)
    {
        if ( ! $force && $this->analyzed) {
            return;
        }

        $this->filter  = $this->request->query(config('jsonapi.request.keys.filter', 'filter'), []);
        $this->include = $this->request->query(config('jsonapi.request.keys.include', 'include'));
        $this->page    = $this->request->query(config('jsonapi.request.keys.page', 'page'), []);
        $this->sort    = $this->request->query(config('jsonapi.request.keys.sort', 'sort'));

        $this->validate();

        $this->analyzed = true;
    }

    /**
     * @return void
     */
    protected function validate()
    {
        $data = [
            'filter'  => $this->filter,
            'include' => $this->include,
            'page'    => $this->page,
            'sort'    => $this->sort,
        ];

        $validator = Validator::make($data, $this->getValidationRules());

        if ($validator->fails()) {
            throw (new JsonApiQueryStringValidationException)
                ->setErrors(
                    $validator->getMessageBag()->toArray()
                );
        }
    }

    /**
     * @return array
     */
    protected function getValidationRules()
    {
        return [
            'filter'      => 'array|nullable',
            'include'     => [ 'regex:' . $this->getValidationRegexForIncludeString(), 'nullable' ],
            'page'        => 'array|nullable',
            'page.number' => [ $this->getValidationStringForPageNumber(), 'nullable' ],
            'page.size'   => [ $this->getValidationStringForPageNumber(), 'nullable' ],
            'page.limit'  => [ $this->getValidationStringForPageNumber(), 'nullable' ],
            'page.offset' => [ $this->getValidationStringForPageOffset(), 'nullable' ],
            'page.cursor' => [ $this->getValidationStringForPageOffset(), 'nullable' ],
            'sort'        => [ 'regex:' . $this->getValidationRegexForSortString(), 'nullable' ],
        ];
    }

    /**
     * @return string
     */
    protected function getValidationRegexForIncludeString()
    {
        return '#^' . $this->getRegexForValidIncludeSegment()
            . '(' . preg_quote($this->includeSeparator)
            . $this->getRegexForValidIncludeSegment() . ')*$#i';
    }

    /**
     * @return string
     */
    protected function getValidationRegexForSortString()
    {
        return '#^' . '-?' . $this->getRegexForValidSortSegment()
            . '(' . preg_quote($this->sortSeparator)
            . '-?' . $this->getRegexForValidSortSegment() . ')*$#i';
    }

    /**
     * @return string
     */
    protected function getRegexForValidIncludeSegment()
    {
        return config('jsonapi.request.validation.query.regex-include-segment', '[a-z0-9.-]+');
    }

    /**
     * @return string
     */
    protected function getRegexForValidSortSegment()
    {
        return config('jsonapi.request.validation.query.regex-sort-segment', '[a-z0-9.-]+');
    }

    /**
     * @return string
     */
    protected function getValidationStringForPageNumber()
    {
        return config('jsonapi.request.validation.query.page-number', 'integer');
    }

    /**
     * @return string
     */
    protected function getValidationStringForPageOffset()
    {
        return config('jsonapi.request.validation.query.page-offset', 'integer');
    }

}
