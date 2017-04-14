<?php
namespace Czim\JsonApi\Encoder\Transformers;

use Czim\JsonApi\Enums\Key;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\AbstractPaginator;
use InvalidArgumentException;

class PaginatedModelsTransformer extends ModelCollectionTransformer
{
    const FIRST_PAGE = 1;

    /**
     * Transforms given data.
     *
     * @param AbstractPaginator $paginator
     * @return array
     */
    public function transform($paginator)
    {
        if ( ! ($paginator instanceof AbstractPaginator)) {
            throw new InvalidArgumentException('ModelTransformer expects AbstractPaginator instance');
        }

        $this->injectPaginationLinks($paginator);

        return parent::transform($paginator->getCollection());
    }

    /**
     * @param AbstractPaginator $paginator
     */
    protected function injectPaginationLinks(AbstractPaginator $paginator)
    {
        $this->encoder->setLink(
            Key::PAGE_FIRST,
            $this->makePaginationLink($paginator, static::FIRST_PAGE)
        );

        if ($paginator->currentPage() - 1 >= static::FIRST_PAGE) {
            $this->encoder->setLink(
                Key::PAGE_PREV,
                $this->makePaginationLink($paginator, $paginator->currentPage())
            );
        }

        if ($paginator instanceof LengthAwarePaginator) {

            if ($paginator->hasMorePages()) {
                $this->encoder->setLink(
                    Key::PAGE_NEXT,
                    $this->makePaginationLink($paginator, min($paginator->currentPage() + 1, $paginator->lastPage()))
                );
            }

            $this->encoder->setLink(
                Key::PAGE_LAST,
                $this->makePaginationLink($paginator, $paginator->lastPage())
            );
        }
    }

    /**
     * @param AbstractPaginator $paginator
     * @param int               $page
     * @return string
     */
    protected function makePaginationLink(AbstractPaginator $paginator, $page)
    {
        if ($topUrl = $this->encoder->getTopResourceUrl()) {
            return $topUrl . '?' . config('jsonapi.request.keys.page') . '=' . $page;
        }

        return $paginator->url($page);
    }

}
