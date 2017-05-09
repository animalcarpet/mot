<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace VehicleApi\Controller;

use DvsaCommon\Http\HttpStatus;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use DvsaEntities\Entity\IncognitoVehicle;
use VehicleApi\InputFilter\MysteryShopperInputFilter;
use VehicleApi\Service\MysteryShopperVehicleService;

/**
 * Class MysteryShopperVehicleController.
 */
class MysteryShopperVehicleController extends AbstractDvsaRestfulController
{
    /**
     * @var MysteryShopperVehicleService
     */
    private $mysteryShopperVehicleService;

    /**
     * @param MysteryShopperVehicleService $mysteryShopperVehicleService
     */
    public function __construct(MysteryShopperVehicleService $mysteryShopperVehicleService)
    {
        $this->mysteryShopperVehicleService = $mysteryShopperVehicleService;
    }

    /**
     * @return \Zend\View\Model\JsonModel
     */
    public function listAction()
    {
        $id = $this->injectVehicleId([])['vehicle_id'];
        $campaigns = $this->mysteryShopperVehicleService->getAllCampaigns($id);
        $campaignsArray = [];

        foreach ($campaigns as $campaign) {
            $campaignsArray[] = $this->filterReturnedFields($campaign);
        }

        $response = ApiResponse::jsonOk($campaignsArray);

        return $response;
    }

    /**
     * @param int $id
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function get($id)
    {
        $current = $this->mysteryShopperVehicleService->getCurrent($id);
        if (null == $current) {
            $this->getResponse()->setStatusCode(HttpStatus::HTTP_UNPROCESSABLE_ENTITY);

            return ApiResponse::jsonError('No Current Campaigns');
        }
        $response = ApiResponse::jsonOk($this->filterReturnedFields($current));

        return $response;
    }

    /**
     * @param array $data
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function create($data)
    {
        $data = $this->injectVehicleId($data);

        $result = $this->mysteryShopperVehicleService->optIn($data);

        if (false === $result) {
            $this->getResponse()->setStatusCode(HttpStatus::HTTP_UNPROCESSABLE_ENTITY);

            return ApiResponse::jsonError(
                $this->mysteryShopperVehicleService->getValidationMessages()
            );
        }

        return ApiResponse::jsonOk(['incognitoVehicleId' => $result->getId()]);
    }

    /**
     * @param int   $id
     * @param array $data
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function update($vehicleId, $data)
    {
        $incognitoVehicleId = $this->params()->fromRoute('incognitoVehicleId');

        if (is_null($incognitoVehicleId)) {
            $this->getResponse()->setStatusCode(HttpStatus::HTTP_BAD_REQUEST);

            return ApiResponse::jsonError(
                'Required id for the incognito vehicle is not provided as part of the url'
            );
        }

        $campaign = $this->mysteryShopperVehicleService->edit($incognitoVehicleId, $data);

        if (false === $campaign) {
            $this->getResponse()->setStatusCode(HttpStatus::HTTP_UNPROCESSABLE_ENTITY);

            return ApiResponse::jsonError(
                $this->mysteryShopperVehicleService->getValidationMessages()
            );
        }

        return ApiResponse::jsonOk(['incognitoVehicle' => $this->filterReturnedFields($campaign)]);
    }

    /**
     * @param int $id
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function delete($id)
    {
        $incognitoVehicleId = $this->params()->fromRoute('incognitoVehicleId');
        $response = ApiResponse::jsonOk(['Soft Delete carried out on Incognito Vehicle' => $incognitoVehicleId]);
        $this->mysteryShopperVehicleService->optOut($incognitoVehicleId);

        return $response;
    }

    /**
     * @param array $data
     *
     * @return mixed
     */
    private function injectVehicleId(array $data)
    {
        $vehicleId = (int) $this->params()->fromRoute('id', 0);

        $data[MysteryShopperInputFilter::FIELD_VEHICLE_ID] = $vehicleId;

        return $data;
    }

    /**
     * @param IncognitoVehicle $incognitoVehicle
     *
     * @return array
     */
    private function filterReturnedFields(IncognitoVehicle $incognitoVehicle)
    {
        $incognitoVehicleId = $incognitoVehicle->getId();
        $startDate = $incognitoVehicle->getStartDate() ?
            $incognitoVehicle->getStartDate()->format('Y-m-d H:i:s') : null;
        $endDate = $incognitoVehicle->getEndDate() ?
            $incognitoVehicle->getEndDate()->format('Y-m-d H:i:s') : null;
        $siteNumber = $incognitoVehicle->getSite() ?
            $incognitoVehicle->getSite()->getSiteNumber() : null;
        $expiryDate = $incognitoVehicle->getExpiryDate() ?
            $incognitoVehicle->getExpiryDate()->format('Y-m-d') : null;
        $testDate = $incognitoVehicle->getTestDate() ?
            $incognitoVehicle->getTestDate()->format('Y-m-d') : null;

        return [
            'incognito_vehicle_id' => $incognitoVehicleId,
            MysteryShopperInputFilter::FIELD_START_DATE => $startDate,
            MysteryShopperInputFilter::FIELD_END_DATE => $endDate,
            MysteryShopperInputFilter::FIELD_SITE_NUMBER => $siteNumber,
            MysteryShopperInputFilter::FIELD_EXPIRY_DATE => $expiryDate,
            MysteryShopperInputFilter::FIELD_TEST_DATE => $testDate,
        ];
    }
}
