<?php
namespace Czim\JsonApi\Encoder\Transformers;

use Czim\JsonApi\Contracts\Support\Error\ErrorDataInterface;
use Czim\JsonApi\Exceptions\JsonApiValidationException;
use Czim\JsonApi\Support\Error\ErrorData;
use InvalidArgumentException;

class ValidationExceptionTransformer extends ErrorDataTransformer
{

    /**
     * Transforms given data.
     *
     * @param mixed $exception
     * @return array
     */
    public function transform($exception)
    {
        if ( ! ($exception instanceof JsonApiValidationException)) {
            throw new InvalidArgumentException("ValidationExceptionTransformer expects JsonApiValidationException instance");
        }

        $data = $this->convertExceptionToErrorData($exception);

        return parent::transform($data);
    }

    /**
     * Converts exception instance to ErrorDataInterface instance
     *
     * @param JsonApiValidationException $exception
     * @return ErrorDataInterface[]
     */
    protected function convertExceptionToErrorData(JsonApiValidationException $exception)
    {
        $errorsData = [];

        $prefix = $exception->getPrefix();

        foreach ($exception->getErrors() as $key => $errors) {

            if (config('jsonapi.transform.group-validation-errors-by-key')) {

                $errorsData[] = new ErrorData([
                    'status' => (string) $this->getStatusCode($exception),
                    'code'   => (string) $exception->getCode(),
                    'title'  => $exception->getMessage(),
                    'detail' => implode("\n", $errors),
                    'source' => [
                        'pointer' => $this->formatAttributePointer($key, $prefix),
                    ],
                ]);
                continue;
            }

            foreach ($errors as $error) {

                $errorsData[] = new ErrorData([
                    'status' => (string) $this->getStatusCode($exception),
                    'code'   => (string) $exception->getCode(),
                    'title'  => $exception->getMessage(),
                    'detail' => $error,
                    'source' => [
                        'pointer' => $this->formatAttributePointer($key, $prefix),
                    ],
                ]);
            }
        }

        return $errorsData;
    }

    /**
     * @param JsonApiValidationException $exception
     * @return int|mixed
     */
    protected function getStatusCode(JsonApiValidationException $exception)
    {
        return $exception->getStatusCode() ?: 500;
    }

    /**
     * Returns pointer notation with optional prefix for source object.
     *
     * @param string      $key
     * @param string|null $prefix
     * @return string
     */
    protected function formatAttributePointer($key, $prefix = null)
    {
        return str_replace('.', '/', $prefix . $key);
    }

}
