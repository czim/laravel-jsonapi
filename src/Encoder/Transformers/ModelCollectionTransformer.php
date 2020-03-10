<?php
namespace Czim\JsonApi\Encoder\Transformers;

use Czim\JsonApi\Contracts\Resource\ResourceInterface;
use Czim\JsonApi\Enums\Key;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class ModelCollectionTransformer extends ModelTransformer
{
    /**
     * The resource that describes the current set of models.
     *
     * @var ResourceInterface
     */
    protected $resource;


    /**
     * Transforms given data.
     *
     * @param Collection $models
     * @return array
     * @throws \Czim\JsonApi\Exceptions\EncodingException
     */
    public function transform($models): array
    {
        if ( ! ($models instanceof Collection)) {
            throw new InvalidArgumentException('ModelTransformer expects collection instance with models');
        }

        if ($models->isEmpty()) {
            return [
                Key::DATA => [],
            ];
        }

        if ($this->isVariable) {
            $this->resource = null;
        } else {
            $this->resource = $this->getResourceForCollection($models);
        }

        $data = [];

        foreach ($models as $model) {
            $data[] = parent::transform($model)[ Key::DATA ];
        }

        return [
            Key::DATA => $data,
        ];
    }

    /**
     * Returns resource that all models in a collection are expected to share.
     *
     * @param Collection $models
     * @return null|ResourceInterface
     */
    protected function getResourceForCollection(Collection $models): ?ResourceInterface
    {
        return $this->encoder->getResourceForModel($models->first());
    }

    /**
     * Overidden to prevent redundant lookups.
     *
     * {@inheritdoc}
     */
    protected function getResourceForModel(Model $model): ?ResourceInterface
    {
        if (null === $this->resource) {
            return parent::getResourceForModel($model);
        }

        return $this->resource;
    }
}
