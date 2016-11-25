<?php

namespace DvsaMotTest\Action;

use Core\Action\NotFoundActionResult;
use Core\Action\RedirectToRoute;
use Core\Action\ViewActionResult;
use Core\Routing\MotTestRouteList;
use Dvsa\Mot\ApiClient\Resource\Item\InternalSearchVehicle;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\DuplicateCertificateSearchType;
use DvsaCommon\Dto\Vehicle\History\VehicleHistoryItemDto;
use DvsaCommon\Dto\Vehicle\History\VehicleHistoryMapper;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\HttpRestJson\Client;
use DvsaCommon\UrlBuilder\VehicleUrlBuilder;
use DvsaMotTest\Flash\VehicleCertificateSearchFlashMessage;
use DvsaMotTest\ViewModel\MotTestCertificate\MotTestCertificateListViewModel;
use DvsaMotTest\ViewModel\MotTestCertificate\MotTestCertificateTableItem;
use DvsaMotTest\ViewModel\MotTestCertificate\VehicleTable;

class VehicleCertificatesAction implements AutoWireableInterface
{
    private $vehicleService;

    private $httpClient;

    private $authorisationService;

    /**
     * VehicleCertificatesAction constructor.
     *
     * @param VehicleService                   $vehicleService
     * @param Client                           $httpClient
     * @param MotAuthorisationServiceInterface $authorisationService
     */
    public function __construct(
        VehicleService $vehicleService,
        Client $httpClient,
        MotAuthorisationServiceInterface $authorisationService
    ) {
        $this->vehicleService = $vehicleService;
        $this->httpClient = $httpClient;
        $this->authorisationService = $authorisationService;
    }

    /**
     * @param string $vrm
     * @param string $vin
     * @param array  $params
     *
     * @return NotFoundActionResult|RedirectToRoute|ViewActionResult
     */
    public function execute($vrm, $vin, array $params)
    {
        $this->authorisationService->assertGranted(PermissionInSystem::CERTIFICATE_SEARCH);

        $vin = $this->cleanQueryParameter($vin);
        $vrm = $this->cleanQueryParameter($vrm);

        if (!$this->checkIfQueryParamsAreValid($vrm, $vin)) {
            return new NotFoundActionResult();
        }

        $viewModel = new MotTestCertificateListViewModel();
        $viewModel->setFoundByRegistration($vrm !== null);

        $searchResult = $this->vehicleService->internalSearch($vrm, $vin);

        /** @var InternalSearchVehicle[] $vehicles */
        $vehicles = $searchResult->getAll();

        if ($searchResult->getCount() == 0) {
            return $this->redirectBackToSearch($vrm, $vin);
        }

        foreach ($vehicles as $vehicle) {
            $vehicleTable = $this->createVehicleViewTableFromDto($vehicle, $params);
            $viewModel->addTable($vehicleTable);
        }

        $actionResult = new ViewActionResult();
        $actionResult->setViewModel($viewModel);
        $actionResult->layout()->setTemplate('layout/layout-govuk.phtml');
        $actionResult->layout()->setPageSubTitle('Duplicate or replacement certificate');
        $actionResult->layout()->setPageTitle('MOT test certificates');
        $actionResult->layout()->setBreadcrumbs(['Duplicate or replacement certificate' => null]);

        return $actionResult;
    }

    private function redirectBackToSearch($vrm, $vin)
    {
        if ($vin == null) {
            $redirect = new RedirectToRoute(
                MotTestRouteList::MOT_TEST_CERTIFICATE_SEARCH_BY_REGISTRATION,
                [],
                [
                    DuplicateCertificateSearchType::SEARCH_TYPE_VRM => $vrm,
                ]
            );
        } else {
            $redirect = new RedirectToRoute(
                MotTestRouteList::MOT_TEST_CERTIFICATE_SEARCH_BY_VIN,
                [],
                [
                    DuplicateCertificateSearchType::SEARCH_TYPE_VIN => $vin,
                ]
            );
        }

        $redirect->addFlashMessage(
            VehicleCertificateSearchFlashMessage::getNamespace(),
            VehicleCertificateSearchFlashMessage::NOT_FOUND
        );

        return $redirect;
    }

    private function cleanQueryParameter($parameter)
    {
        $parameter = trim($parameter);

        if ($parameter === '') {
            $parameter = null;
        }

        return $parameter;
    }

    private function checkIfQueryParamsAreValid($vrm, $vin)
    {
        // we only expect one parameter to be provided
        return $vrm === null xor $vin === null;
    }

    /**
     * @param InternalSearchVehicle $vehicle
     * @param $paramsForSearchBy
     *
     * @return VehicleTable
     */
    private function createVehicleViewTableFromDto(InternalSearchVehicle $vehicle, $paramsForSearchBy)
    {
        $vehicleTable = new VehicleTable();
        $vehicleTable->setMake($vehicle->getMake());
        $vehicleTable->setModel($vehicle->getModel());
        $vehicleTable->setRegistration($vehicle->getRegistration());
        $vehicleTable->setVin($vehicle->getVin());

        $vehicleCertificates = $this->getCertificatesFromApi($vehicle->getId());

        foreach ($vehicleCertificates as $item) {
            $vehicleCertificateRow = new MotTestCertificateTableItem();
            $vehicleCertificateRow->setSiteName($item->getSiteName());
            $vehicleCertificateRow->setTestStatus($item->getDisplayStatus());
            $vehicleCertificateRow->setDateOfTest($item->getDisplayIssuedDate());
            $vehicleCertificateRow->setSiteAddress($item->getSiteAddress());
            $vehicleCertificateRow->setTestNumber($item->getMotTestNumber());
            $vehicleCertificateRow->setParamsForSearchBy($paramsForSearchBy);

            $vehicleTable->addRow($vehicleCertificateRow);
        }

        return $vehicleTable;
    }

    /**
     * @param $vehicleId
     *
     * @return VehicleHistoryItemDto[] | \ArrayIterator
     */
    private function getCertificatesFromApi($vehicleId)
    {
        $vtsId = null;

        $apiUrl = VehicleUrlBuilder::testHistory($vehicleId, $vtsId);
        $apiResult = $this->httpClient->get($apiUrl);

        $vehicleHistory = (new VehicleHistoryMapper())->fromArrayToDto($apiResult['data'], 0);

        return $vehicleHistory->getIterator();
    }
}
