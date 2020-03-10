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
    public function links();

    /**
     * @return string
     */
    public function status();

    /**
     * @return string
     */
    public function code();

    /**
     * @return string
     */
    public function title();

    /**
     * @return string
     */
    public function detail();

    /**
     * @return array
     */
    public function source();

    /**
     * @return array
     */
    public function meta();

    /**
     * Returns array without empty values.
     *
     * @return array
     */
    public function toCleanArray();

}
