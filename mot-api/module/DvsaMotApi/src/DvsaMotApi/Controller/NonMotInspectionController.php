<?php

namespace DvsaMotApi\Controller;

use DvsaCommon\Auth\AbstractMotAuthorisationService;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApi\Service\Exception\ForbiddenException;
use DvsaMotApi\Service\MotTestService;

class NonMotInspectionController extends AbstractDvsaRestfulController
{
    /**
     * @var MotTestService
     */
    private $motTestService;
    /**
     * @var AbstractMotAuthorisationService
     */
    private $authorisationService;

    public function __construct(
        MotTestService $motTestService,
        AbstractMotAuthorisationService $authorisationService)
    {
        $this->motTestService = $motTestService;
        $this->authorisationService = $authorisationService;
    }

    /**
     * @param array $data
     *
     * @return array|\Zend\View\Model\JsonModel
     *
     * @throws ForbiddenException
     */
    public function create($data)
    {
        if (!$this->authorisationService->isGranted(PermissionInSystem::ENFORCEMENT_NON_MOT_TEST_PERFORM)) {
            throw new ForbiddenException('Only VE user can create Non MOT inspection');
        }

        $motTest = $this->motTestService->createMotTest($data);

        return ApiResponse::jsonOk(['motTestNumber' => $motTest->getNumber()]);
    }
}
