<?php
namespace Czim\JsonApi\Contracts;

use Czim\DataObject\Contracts\DataObjectInterface;

interface JsonApiCurrentMetaInterface extends DataObjectInterface
{

    /**
     * Clears all the attributes in the data object
     *
     * @return $this;
     */
    public function clearAttributes();

}
