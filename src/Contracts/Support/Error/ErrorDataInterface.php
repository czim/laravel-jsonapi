<?php
namespace Czim\JsonApi\Contracts\Support\Error;

interface ErrorDataInterface
{
    /**
     * @return mixed
     */
    public function id();

    /**
     * @return array
     */
    public function links(): array;

    /**
     * @return string
     */
    public function status(): string;

    /**
     * @return string
     */
    public function code(): string;

    /**
     * @return string
     */
    public function title(): string;

    /**
     * @return string
     */
    public function detail(): string;

    /**
     * @return array
     */
    public function source(): array;

    /**
     * @return array
     */
    public function meta(): array;

    /**
     * Returns array without empty values.
     *
     * @return array
     */
    public function toCleanArray(): array;
}
