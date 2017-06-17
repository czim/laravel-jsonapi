<?php
namespace Czim\JsonApi\Encoder\Transformers;

use Czim\JsonApi\Contracts\Support\Error\ErrorDataInterface;
use Czim\JsonApi\Exceptions\JsonApiValidationException;
use Czim\JsonApi\Support\Error\ErrorData;
use Exception;
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
     * @param Exception $exception
     * @return int|mixed
     */
    protected function getStatusCode(Exception $exception)
    {
        // special case: fully formed response exception (laravel 5.2 validation)
        if (is_a($exception, \Illuminate\Http\Exceptions\HttpResponseException::class)) {
            /** @var \Illuminate\Http\Exceptions\HttpResponseException $exception */
            return $exception->getResponse()->getStatusCode();
        }

        $mapping = config('jsonapi.exceptions.status', []);

        if (array_key_exists(get_class($exception), $mapping)) {
            return $mapping[ get_class($exception) ];
        }

        if (method_exists($exception, 'getStatusCode')) {
            return $exception->getStatusCode();
        }

        return 500;
    }

    /**
     * @param Exception $exception
     * @return string
     */
    protected function getTitle(Exception $exception)
    {
        return ucfirst(snake_case(class_basename($exception), ' '));
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
