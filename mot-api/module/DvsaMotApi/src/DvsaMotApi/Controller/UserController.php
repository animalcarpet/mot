<?php

namespace DvsaMotApi\Controller;

use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\Validator\UsernameValidator;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use DvsaMotApi\Service\UserService;

/**
 * Class UserController.
 */
class UserController extends AbstractDvsaRestfulController
{
    /**
     * @var UsernameValidator
     */
    protected $usernameValidator;

    /**
     * @param \DvsaCommon\Validator\UsernameValidator $usernameValidator
     */
    public function __construct(UsernameValidator $usernameValidator)
    {
        $this->usernameValidator = $usernameValidator;
    }

    /**
     * Retrieve the route match/query parameter name containing the identifier.
     *
     * @return string
     */
    public function getIdentifierName()
    {
        return 'username';
    }

    /**
     * Returning a list of users is currently disabled. We override the default the AbstractDvsaRestfulController
     * implementation by returning a 404 instead of the default 405 (HTTP_METHOD_NOT_ALLOWED).
     *
     * @return mixed
     */
    public function getList()
    {
        return self::returnMethodNotFoundModel();
    }

    /**
     * @param mixed $username
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function get($username)
    {
        if (true === $this->usernameValidator->isValid($username)) {
            try {
                $data = $this->getUserService()->getUserData($username);

                return ApiResponse::jsonOk($data);
            } catch (UnauthorisedException $e) {
                // NOTE: 401 to 404 transformation to meet acceptance criteria for VM-8488.
                return $this->createNotFoundResponseModel();
            }
        } else {
            // Request matched and met syntactic contract but validation failed.
            $validationMessages = ['username' => $this->usernameValidator->getMessages()];

            return $this->createValidationProblemResponseModel($validationMessages);
        }
    }

    /**
     * @param mixed $data
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function create($data)
    {
        $user = $this->getUserService()->create($data);

        return ApiResponse::jsonOk($user);
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->getServiceLocator()->get(UserService::class);
    }
}
