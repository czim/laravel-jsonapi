<?php
namespace Czim\JsonApi\Support\Request;

use Czim\JsonApi\Contracts\Support\Request\RequestQueryParserInterface;
use Czim\JsonApi\Exceptions\JsonApiQueryStringValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Validator;

/**
 * Parses contextual data set in the query string (filters, includes, sorting, etc).
 */
class RequestQueryParser implements RequestQueryParserInterface
{
    public const DEFAULT_INCLUDE_SEPARATOR = ',';
    public const DEFAULT_SORT_SEPARATOR    = ',';

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
    public function getFilter(): array
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
    public function getFilterValue(string $key, $default = null)
    {
        return Arr::get($this->getFilter(), $key, $default);
    }

    /**
     * Returns raw JSON-API include string.
     *
     * @return string
     */
    public function getRawIncludes(): ?string
    {
        $this->analyze();

        return $this->include;
    }

    /**
     * Returns JSON-API includes as array of strings.
     *
     * @return string[]
     */
    public function getIncludes(): array
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
    public function getPageData(): array
    {
        $this->analyze();

        return $this->page;
    }

    public function getPageNumber(): int
    {
        return (int) Arr::get($this->getPageData(), 'number', 1);
    }

    public function getPageSize(): int
    {
        return (int) Arr::get($this->getPageData(), 'size');
    }

    public function getPageOffset(): int
    {
        return (int) Arr::get($this->getPageData(), 'offset', 0);
    }

    public function getPageLimit(): int
    {
        return (int) Arr::get($this->getPageData(), 'limit');
    }

    /**
     * @return mixed
     */
    public function getPageCursor()
    {
        return (int) Arr::get($this->getPageData(), 'cursor');
    }

    /**
     * Returns raw sort string.
     *
     * @return string|null
     */
    public function getRawSort(): ?string
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
    public function getSort(): array
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
    protected function analyze(bool $force = false): void
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

    protected function validate(): void
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

    protected function getValidationRules(): array
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

    protected function getValidationRegexForIncludeString(): string
    {
        return '#^' . $this->getRegexForValidIncludeSegment()
            . '(' . preg_quote($this->includeSeparator)
            . $this->getRegexForValidIncludeSegment() . ')*$#i';
    }

    protected function getValidationRegexForSortString(): string
    {
        return '#^' . '-?' . $this->getRegexForValidSortSegment()
            . '(' . preg_quote($this->sortSeparator)
            . '-?' . $this->getRegexForValidSortSegment() . ')*$#i';
    }

    protected function getRegexForValidIncludeSegment(): string
    {
        return config('jsonapi.request.validation.query.regex-include-segment', '[a-z0-9.-]+');
    }

    protected function getRegexForValidSortSegment(): string
    {
        return config('jsonapi.request.validation.query.regex-sort-segment', '[a-z0-9.-]+');
    }

    protected function getValidationStringForPageNumber(): string
    {
        return config('jsonapi.request.validation.query.page-number', 'integer');
    }

    protected function getValidationStringForPageOffset(): string
    {
        return config('jsonapi.request.validation.query.page-offset', 'integer');
    }
}
