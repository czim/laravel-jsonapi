<?php
namespace Czim\JsonApi\Contracts;

interface ResourceStaticRelationsInterface
{

    /**
     * Returns list of static relations
     *
     * @return array
     */
    public function getStaticRelations();

    /**
     * Returns links for static relations
     *
     * @param string $relation
     * @return array    with LinkInterface related key (schema data)
     */
    public function getStaticRelationLinks($relation);

}
