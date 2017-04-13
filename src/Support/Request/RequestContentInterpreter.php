<?php
namespace Czim\JsonApi\Support\Request;

use Illuminate\Http\Request;

class RequestContentInterpreter
{

    /**
     * @var Request
     */
    protected $request;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Returns top-level meta section.
     *
     * @return array
     */
    public function getMeta()
    {
        return $this->request->input('meta', []);
    }

    /**
     * Returns top level links section.
     *
     * @return array
     */
    public function getLinks()
    {
        return $this->request->input('links', []);
    }

    /**
     * Returns top-level data section.
     *
     * @return array
     */
    public function getData()
    {
        return $this->request->input('data', []);
    }

    /**
     * @return string|null
     */
    public function getId()
    {
        return array_get($this->getData(), 'id', null);
    }

    /**
     * @return string|null
     */
    public function getType()
    {
        return array_get($this->getData(), 'type', null);
    }

}
