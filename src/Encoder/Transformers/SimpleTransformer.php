<?php
namespace Czim\JsonApi\Encoder\Transformers;

use Illuminate\Contracts\Support\Arrayable;
use Czim\JsonApi\Exceptions\EncodingException;

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
