<?php
namespace Czim\JsonApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Czim\JsonApi\Support\Request\RequestQueryParser;

class JsonApiRequest extends FormRequest
{

    /**
     * @var RequestQueryParser
     */
    protected $jsonApiQuery;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $query = array(), array $request = array(), array $attributes = array(), array $cookies = array(), array $files = array(), array $server = array(), $content = null)
    {
        parent::__construct();

        $this->jsonApiQuery = new RequestQueryParser($this);
    }

    /**
     * @return RequestQueryParser
     */
    public function jsonApiQuery()
    {
        return $this->jsonApiQuery;
    }


}
