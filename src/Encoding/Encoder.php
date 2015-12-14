<?php
namespace Czim\JsonApi\Encoding;

use Czim\JsonApi\Encoding\Factory;
use Neomerx\JsonApi\Contracts\Factories\FactoryInterface;

/**
 * Extended to be able to use our own Factory class
 */
class Encoder extends \Neomerx\JsonApi\Encoder\Encoder
{

    /**
     * @return FactoryInterface
     */
    protected static function getFactory()
    {
        return new Factory;
    }

}
