<?php

namespace DvsaMotApi\Controller;

use DvsaAuthentication\Login\LoginService;
use DvsaCommon\Http\HttpStatus;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use Zend\Http\Response;

class SessionConfirmationController extends AbstractDvsaRestfulController
{
    private $loginService;

    public function __construct(Response $response, LoginService $loginService)
    {
        $this->response = $response;
        $this->loginService = $loginService;
    }

    public function create($data)
    {
        $password = isset($data['password']) ? $data['password'] : '';
        $statusCode = HttpStatus::HTTP_UNPROCESSABLE_ENTITY;

        if ($this->loginService->confirmPassword($password)) {
            $statusCode = Response::STATUS_CODE_200;
        }

        $this->response->setStatusCode($statusCode);

        return ApiResponse::jsonOk();
    }
}
