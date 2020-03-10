<?php
namespace Czim\JsonApi\Support\Error;

use Czim\DataObject\AbstractDataObject;
use Czim\JsonApi\Contracts\Support\Error\ErrorDataInterface;

/**
 * @property mixed $id
 * @property array $links       array with 'about' key
 * @property string $status
 * @property string $code
 * @property string $title
 * @property string $detail
 * @property array  $source     array with 'pointer' [RFC6901], 'parameter'
 * @property array  $meta
 */
class ErrorData extends AbstractDataObject implements ErrorDataInterface
{
    /**
     * @return mixed
     */
    public function id()
    {
        return $this->id ?: '';
    }

    /**
     * @return array
     */
    public function links(): array
    {
        return $this->links ?: [];
    }

    /**
     * @return string
     */
    public function status(): string
    {
        return (string) $this->status ?: '';
    }

    /**
     * @return string
     */
    public function code(): string
    {
        return (string) $this->code ?: '';
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->title ?: '';
    }

    /**
     * @return string
     */
    public function detail(): string
    {
        return $this->detail ?: '';
    }

    /**
     * @return array
     */
    public function source(): array
    {
        return $this->source ?: [];
    }

    /**
     * @return array
     */
    public function meta(): array
    {
        return $this->meta ?: [];
    }

    /**
     * Returns array without empty values.
     *
     * @return array
     */
    public function toCleanArray(): array
    {
        return array_filter($this->toArray());
    }
}
