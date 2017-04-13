<?php
namespace Czim\JsonApi\Encoder\Transformers;

use Czim\JsonApi\Exceptions\EncodingException;
use Illuminate\Contracts\Support\Arrayable;

class SimpleTransformer extends AbstractTransformer
{

    /**
     * Transforms given data.
     *
     * @param mixed $data
     * @return array
     * @throws EncodingException
     */
    public function transform($data)
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
