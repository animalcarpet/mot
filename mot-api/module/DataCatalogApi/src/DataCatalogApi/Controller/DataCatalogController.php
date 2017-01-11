<?php
namespace DataCatalogApi\Controller;

use DataCatalogApi\Service\DataCatalogService;
use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;

use Zend\View\Model\JsonModel;

/**
 * Class DataCatalogController
 *
 * @package DataCatalogApi\Controller
 */
class DataCatalogController extends AbstractDvsaRestfulController
{
    public function getList()
    {
        /** @var \DataCatalogApi\Service\DataCatalogService $service */
        $service = $this->getServiceLocator()->get(DataCatalogService::class);
        return ApiResponse::jsonOk(
            [
                'brakeTestType'         => $service->getBrakeTestType(),
                'categories'            => $service->getEnforcementDecisionCategoryData(),
                'colours'               => $service->getColours(),
                'countryOfRegistration' => $service->getCountriesOfRegistration(),
                'decisions'             => $service->getEnforcementDecisionData(),
                'demoTestResult'        => $service->getDemoTestResultData(),
                'equipmentModelStatus'  => $service->getEquipmentModelStatus(),
                'eventTypesWithOutcomes' => $service->getEventTypesWithOutcomes(),
                'fuelTypes'             => $service->getFuelTypes(),
                'motTestType'           => $service->getMotTestTypes(),
                'organisationBusinessRole' => $service->getOrganisationBusinessRoles(),
                'outcomes'              => $service->getEnforcementDecisionOutcomeData(),
                'personSystemRoles'     => $service->getPersonSystemRoles(),
                'qualificationStatus'   => $service->getQualificationStatus(),
                'reasonsForCancel'      => $service->getMotTestReasonsForCancel(),
                'reasonsForEmptyVIN'    => $service->getReasonsForEmptyVIN(),
                'reasonsForEmptyVRM'    => $service->getReasonsForEmptyVRM(),
                'reasonsForRefusal'     => $service->getReasonsForRefusal(),
                'reasonsForSiteVisit'   => $service->getReasonForSiteVisitData(),
                'reinspections'         => $service->getEnforcementDecisionReinspectionOutcomeData(),
                'scores'                => $service->getEnforcementDecisionScoreData(),
                'siteBusinessRole'      => $service->getSiteBusinessRoles(),
                'testerStatus'          => [],
                'siteStatus'            => $service->getSiteStatuses(),
                'transmissionType'      => $service->getTransmissionTypes(),
                'vehicleClass'          => $service->getVehicleClasses(),
                'visitOutcomes'         => $service->getSiteAssessmentVisitOutcomeData(),
                'BusinessRoles'         => $service->getBusinessRoles(),
            ]
        );
    }
}
