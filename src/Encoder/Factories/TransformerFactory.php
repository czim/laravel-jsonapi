<?php
namespace Czim\JsonApi\Encoder\Factories;

use Czim\JsonApi\Contracts\Encoder\TransformerFactoryInterface;
use Czim\JsonApi\Contracts\Encoder\TransformerInterface;
use Czim\JsonApi\Encoder\Transformers;
use Exception;
use Illuminate\Database\Eloquent\Collection as ModelCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;

class TransformerFactory implements TransformerFactoryInterface
{
    /**
     * Makes a transformer for given data.
     *
     * @param mixed $data
     * @return TransformerInterface
     */
    public function makeFor($data): TransformerInterface
    {
        $class = $this->determineTransformerClass($data);

        return app($class);
    }


    /**
     * Returns classname of transformer to make for given data.
     *
     * @param mixed $data
     * @return string
     */
    protected function determineTransformerClass($data): string
    {
        // Specific class fqn map to transformers with is_a() checking

        if (is_object($data)) {
            if ($class = $this->determineMappedTransformer($data)) {
                return $class;
            }
        }

        // Fallback: pick best available by type

        if ($data instanceof Model) {
            return Transformers\ModelTransformer::class;
        }

        if ($data instanceof ModelCollection) {
            return Transformers\ModelCollectionTransformer::class;
        }

        if ($data instanceof Exception) {
            return Transformers\ExceptionTransformer::class;
        }

        // If we get a collection with only models in it, treat it as a model collection
        if ($data instanceof Collection && $this->isCollectionWithOnlyModels($data)) {
            return Transformers\ModelCollectionTransformer::class;
        }

        if ($data instanceof AbstractPaginator && $this->isPaginatorWithOnlyModels($data)) {
            return Transformers\PaginatedModelsTransformer::class;
        }

        return Transformers\SimpleTransformer::class;
    }

    protected function isCollectionWithOnlyModels(Collection $collection): bool
    {
        $filtered = $collection->filter(function ($item) { return $item instanceof Model; });

        return $collection->count() === $filtered->count();
    }

    protected function isPaginatorWithOnlyModels(AbstractPaginator $paginator): bool
    {
        $collection = $paginator->getCollection();

        if ($collection instanceof ModelCollection) {
            return true;
        }

        return $this->isCollectionWithOnlyModels($collection);
    }

    /**
     * Returns mapped transformer class, if a match could be found.
     *
     * @param object $object
     * @return null|string
     */
    protected function determineMappedTransformer($object): ?string
    {
        $map = config('jsonapi.transform.map', []);

        if (empty($map)) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        foreach ($map as $class => $transformer) {

            if (is_a($object, $class)) {
                return $transformer;
            }
        }

        return null;
    }
}
