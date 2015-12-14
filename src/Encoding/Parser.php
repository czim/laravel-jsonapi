<?php
namespace Czim\JsonApi\Encoding;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Collection;
use Neomerx\JsonApi\Contracts\Encoder\Parser\ParserFactoryInterface;
use Neomerx\JsonApi\Contracts\Encoder\Parser\ParserManagerInterface;
use Neomerx\JsonApi\Contracts\Encoder\Stack\StackFactoryInterface;
use Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaFactoryInterface;

class Parser extends \Neomerx\JsonApi\Encoder\Parser\Parser
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Overriden because $container is stored privately in the parent analyzer,
     * which shows you how inconvenient that can be.
     *
     * @param ParserFactoryInterface $parserFactory
     * @param StackFactoryInterface  $stackFactory
     * @param SchemaFactoryInterface $schemaFactory
     * @param ContainerInterface     $container
     * @param ParserManagerInterface $manager
     */
    public function __construct(
        ParserFactoryInterface $parserFactory,
        StackFactoryInterface $stackFactory,
        SchemaFactoryInterface $schemaFactory,
        ContainerInterface $container,
        ParserManagerInterface $manager
    ) {
        $this->manager       = $manager;
        $this->container     = $container;
        $this->stackFactory  = $stackFactory;
        $this->parserFactory = $parserFactory;
        $this->schemaFactory = $schemaFactory;

        parent::__construct($parserFactory, $stackFactory, $schemaFactory, $container, $manager);
    }

    /**
     * Overridden so sensible schema handling can be used for:
     * Pivot objects and Eloquent Collections. Anything else
     * can be handled normally.
     *
     * @return array
     */
    protected function analyzeCurrentData()
    {
        $relationship = $this->stack->end()->getRelationship();

        $data = $relationship->isShowData() === true
                ?   $relationship->getData()
                :    null;

        if ($data instanceof Collection) {

            $firstItem       = null;
            $isEmpty         = (count($data) === 0);
            $traversableData = $data;

            if ($isEmpty === false) {
                $firstItem = $data->first();
            }

            if ($firstItem === null) {
                $traversableData = [];
            }

            return [ $isEmpty, true, $traversableData ];

        } elseif ($data instanceof Pivot) {

            return [ true, false, [] ];
        }

        return parent::analyzeCurrentData();
    }

}

