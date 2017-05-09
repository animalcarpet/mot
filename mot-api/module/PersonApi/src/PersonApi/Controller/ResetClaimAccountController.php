<?php

namespace PersonApi\Controller;

use Doctrine\ORM\EntityManager;
use UserApi\HelpDesk\Service\ResetClaimAccountService;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;

/**
 * Class ResetClaimAccountController.
 */
class ResetClaimAccountController extends AbstractDvsaRestfulController
{
    /** @var ResetClaimAccountService */
    private $resetClaimAccountService;
    /** @var EntityManager */
    private $entityManager;

    /**
     * Constructor of the ResetClaimAccountController.
     *
     * @param EntityManager            $entityManager
     * @param ResetClaimAccountService $service
     */
    public function __construct(EntityManager $entityManager, ResetClaimAccountService $service)
    {
        $this->entityManager = $entityManager;
        $this->resetClaimAccountService = $service;
    }

    /**
     * End point to reset an account by the person Id
     * This function will return true if succeed or throw an exception.
     *
     * @param int $id
     *
     * @return \Zend\View\Model\JsonModel
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function get($id)
    {
        $result = $this->resetClaimAccountService->resetClaimAccount($id, $this->getUsername());
        $this->entityManager->flush();

        return ApiResponse::jsonOk($result);
    }
}
