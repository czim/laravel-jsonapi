<?php
namespace Czim\JsonApi\Encoder\Transformers;

use Illuminate\Contracts\Support\Arrayable;

class SimpleTransformer extends AbstractTransformer
{
    /**
     * Transforms given data.
     *
     * @param mixed $data
     * @return array
     */
    public function transform($data): array
    {
        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        }

        if ( ! is_array($data)) {
            $data = (array) $data;
        }

        return compact('data');
    }
}
