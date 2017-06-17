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
     * @param ErrorDataInterface|ErrorDataInterface[] $errors
     * @return array
     */
    public function transform($errors)
    {
        if ( ! is_array($errors)) {
            $errors = [ $errors ];
        }

        $this->checkErrorDataArray($errors);

        return [
            Key::ERRORS => array_map(
                function (ErrorDataInterface $error) {
                    return $error->toCleanArray();
                },
                $errors
            ),
        ];
    }

    /**
     * Checks all error objects in a given array, throw exception if one does not match expected interface.
     *
     * @param array $errors
     */
    protected function checkErrorDataArray(array $errors)
    {
        foreach ($errors as $error) {

            if ( ! ($error instanceof ErrorDataInterface)) {
                throw new InvalidArgumentException("ErrorDataTransformer expects (array of) ErrorDataInterface instance(s)");
            }
        }
    }

}
