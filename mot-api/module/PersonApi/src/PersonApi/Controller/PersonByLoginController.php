<?php

namespace PersonApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use PersonApi\Service\PersonService;
use DvsaCommon\Validator\UsernameValidator;
use DvsaCommon\Exception\UnauthorisedException;

/**
 * Class PersonByLoginController.
 */
class PersonByLoginController extends AbstractDvsaRestfulController
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

        // Login is the same as username.
        $this->setIdentifierName('login');
    }

    /**
     * @param int $login
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function get($login)
    {
        if (true === $this->usernameValidator->isValid($login)) {
            try {
                $data = $this->getPersonService()->getPersonByIdentifierArray($login);

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
     * @return PersonService
     */
    protected function getPersonService()
    {
        return $this->getServiceLocator()->get(PersonService::class);
    }
}
