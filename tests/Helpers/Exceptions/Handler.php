<?php
namespace Czim\JsonApi\Test\Helpers\Exceptions;

use Exception;

class Handler extends \Illuminate\Foundation\Exceptions\Handler
{

    /**
     * Render an exception into a response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Exception  $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Exception $e)
    {
        return jsonapi_error($e);
    }

}
