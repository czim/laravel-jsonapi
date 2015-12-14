<?php
namespace Czim\JsonApi\Encoding;

use Czim\JsonApi\Schema\Container;
use Neomerx\JsonApi\Contracts\Encoder\Parser\ParserManagerInterface;
use Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use Neomerx\JsonApi\Encoder\EncoderOptions;

/**
 * Extended to break break/replace package tight coupling with our own tight coupling...
 */
class Factory extends \Neomerx\JsonApi\Factories\Factory
{

    /**
     * @inheritdoc
     */
    public function createEncoder(ContainerInterface $container, EncoderOptions $encoderOptions = null)
    {
        return new Encoder($this, $container, $encoderOptions);
    }

    /**
     * @inheritdoc
     */
    public function createParser(ContainerInterface $container, ParserManagerInterface $manager)
    {
        return new Parser($this, $this, $this, $container, $manager);
    }

    /**
     * @inheritdoc
     */
    public function createContainer(array $providers = [])
    {
        return new Container($this, $providers);
    }

}
