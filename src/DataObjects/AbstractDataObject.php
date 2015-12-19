<?php
namespace Czim\JsonApi\DataObjects;

use Czim\DataObject\AbstractDataObject as CzimAbstractDataObject;

abstract class AbstractDataObject extends CzimAbstractDataObject
{

    /**
     * Clears all the attributes in the data object
     *
     * @return $this;
     */
    public function clearAttributes()
    {
        $this->attributes = [];

        return $this;
    }

}
