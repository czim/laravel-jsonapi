<?php
namespace Czim\JsonApi\Encoder\Transformers;

use Czim\JsonApi\Contracts\Support\Error\ErrorDataInterface;
use Czim\JsonApi\Enums\Key;
use InvalidArgumentException;

class ErrorDataTransformer extends AbstractTransformer
{

    /**
     * Transforms given data.
     *
     * @param ErrorDataInterface $error
     * @return array
     */
    public function transform($error)
    {
        if ( ! ($error instanceof ErrorDataInterface)) {
            throw new InvalidArgumentException("ErrorDataTransformer expects ErrorDataInterface instance");
        }

        return [
            Key::ERROR => $error->toCleanArray()
        ];
    }

}
