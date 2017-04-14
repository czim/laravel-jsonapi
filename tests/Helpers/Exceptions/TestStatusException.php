<?php
namespace Czim\JsonApi\Test\Helpers\Exceptions;

class TestStatusException extends \Exception
{

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return 418;
    }

}
