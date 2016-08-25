<?php

namespace Core\Action;

use Zend\Http\Response;

class HttpResponseResult extends AbstractRedirectActionResult
{
    private $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}
