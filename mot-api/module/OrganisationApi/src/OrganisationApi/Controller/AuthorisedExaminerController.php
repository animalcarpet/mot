<?php

namespace OrganisationApi\Controller;

use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Utility\DtoHydrator;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use OrganisationApi\Service\AuthorisedExaminerService;
use OrganisationApi\Service\UpdateAeDetailsService;

/**
 * Api controller for AuthorisedExaminers.
 */
class AuthorisedExaminerController extends AbstractDvsaRestfulController implements AutoWireableInterface
{
    const AE_NUMBER_PARAM = 'number';

    /**
     * @var AuthorisedExaminerService
     */
    private $service;

    /**
     * @var UpdateAeDetailsService
     */
    private $updateService;

    public function __construct(AuthorisedExaminerService $service, UpdateAeDetailsService $updateService)
    {
        $this->service = $service;
        $this->updateService = $updateService;
    }

    public function create($data)
    {
        /** @var OrganisationDto $dto */
        $dto = DtoHydrator::jsonToDto($data);
        $result = $this->service->create($dto);

        return ApiResponse::jsonOk($result);
    }

    public function patch($id, $data)
    {
        $this->updateService->update($id, $data);

        return ApiResponse::jsonOk([]);
    }

    public function get($id)
    {
        return ApiResponse::jsonOk($this->service->get($id));
    }

    /**
     * Given an identifying string, locate the associated AE (Organisation)
     * or return a 404 indicating no match or an error occurred.
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function getAuthorisedExaminerByNumberAction()
    {
        $aeNumber = $this->params()->fromRoute('number', $this->getRequest()->getQuery(self::AE_NUMBER_PARAM));

        $orgData = $this->service->getByNumber($aeNumber);

        return ApiResponse::jsonOk($orgData);
    }
}
