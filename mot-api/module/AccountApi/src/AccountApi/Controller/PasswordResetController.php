<?php

namespace AccountApi\Controller;

use AccountApi\Service\TokenService;
use Doctrine\ORM\EntityManager;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;

/**
 * This controller allow us to create/validate a token to allow the user to reset his password.
 * For the moment only by mail so the user must have specified his email before.
 *
 * Class PasswordResetController
 */
class PasswordResetController extends AbstractDvsaRestfulController
{
    /** @var TokenService */
    protected $tokenService;
    /** @var EntityManager */
    protected $entityManager;

    public function __construct(
        TokenService $tokenService,
        EntityManager $entityManager
    ) {
        $this->tokenService = $tokenService;
        $this->entityManager = $entityManager;

        $this->setIdentifierName('token');
    }

    /**
     * This function is going to generate a token and send the link to reset his password to the user.
     *
     * @param array $data
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function create($data)
    {
        $res = $this->tokenService->createTokenAndEmailForgottenLink($data['userId']);
        $this->entityManager->flush();

        return ApiResponse::jsonOk($res);
    }

    /**
     * This function validate if the token that the user use is valid.
     *
     * @param string $token
     *
     * @return \Zend\View\Model\JsonModel
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function get($token)
    {
        return ApiResponse::jsonOk($this->tokenService->getToken($token));
    }
}
