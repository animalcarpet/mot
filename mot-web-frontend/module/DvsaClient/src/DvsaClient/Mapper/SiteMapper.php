<?php

namespace DvsaClient\Mapper;

use DvsaClient\Entity\VehicleTestingStation;
use DvsaCommon\Dto\Site\EnforcementSiteAssessmentDto;
use DvsaCommon\Dto\Site\SiteListDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Dto\Site\SiteContactPatchDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\UrlBuilder\VehicleTestingStationUrlBuilder;
use DvsaCommon\Utility\DtoHydrator;

/**
 * Class SiteMapper.
 */
class SiteMapper extends DtoMapper implements AutoWireableInterface
{
    protected $entityClass = VehicleTestingStation::class;

    /**
     * @param int
     *
     * @return VehicleTestingStationDto
     */
    public function getById($vtsId)
    {
        $apiUrl = VehicleTestingStationUrlBuilder::vtsById($vtsId);

        return DtoHydrator::jsonToDto($this->get($apiUrl));
    }

    /**
     * @param VehicleTestingStationDto $dto
     *
     * @return int the Site id
     */
    public function create(VehicleTestingStationDto $dto)
    {
        $url = VehicleTestingStationUrlBuilder::vtsById();

        return $this->post($url, DtoHydrator::dtoToJson($dto));
    }

    public function validate(VehicleTestingStationDto $dto)
    {
        $url = VehicleTestingStationUrlBuilder::vtsById();
        $dto->setIsNeedConfirmation(true);

        return $this->post($url, DtoHydrator::dtoToJson($dto));
    }

    public function validateTestingFacilities($siteId, VehicleTestingStationDto $dto)
    {
        $url = VehicleTestingStationUrlBuilder::updateTestingFacilities($siteId);
        $dto->setIsNeedConfirmation(true);

        return $this->put($url, DtoHydrator::dtoToJson($dto));
    }

    /**
     * @param int   $id
     * @param array $data
     *
     * @return int
     */
    public function update($id, $data)
    {
        $apiUrl = VehicleTestingStationUrlBuilder::vtsById($id);

        $response = $this->put($apiUrl, $data);

        return $response['id'];
    }

    /**
     * Update testing facilities for specified site.
     *
     * @param $siteId
     * @param VehicleTestingStationDto $dto
     *
     * @return mixed
     */
    public function updateTestingFacilities($siteId, VehicleTestingStationDto $dto)
    {
        $apiUrl = VehicleTestingStationUrlBuilder::updateTestingFacilities($siteId);

        return $this->client->put($apiUrl, DtoHydrator::dtoToJson($dto));
    }

    /**
     * @param int   $id
     * @param array $data
     */
    public function saveDefaultBrakeTests($id, $data)
    {
        $apiUrl = VehicleTestingStationUrlBuilder::defaultBrakeTests($id);
        $this->put($apiUrl, $data);
    }

    /**
     * @param array $params
     *
     * @return SiteListDto
     */
    public function search($params)
    {
        $apiUrl = VehicleTestingStationUrlBuilder::search();

        return $this->post($apiUrl, $params);
    }

    public function updateVtsProperty($vtsId, $property, $value)
    {
        $apiUrl = VehicleTestingStationUrlBuilder::vtsDetails($vtsId);

        return $this->patch($apiUrl, [$property => $value, '_class' => VehicleTestingStationDto::class]);
    }

    public function updateVtsContactProperty($vtsId, $property, $value)
    {
        $apiUrl = VehicleTestingStationUrlBuilder::contactUpdate($vtsId);

        return $this->patch($apiUrl, [$property => $value, '_class' => SiteContactPatchDto::class]);
    }

    public function validateSiteAssessment($siteId, EnforcementSiteAssessmentDto $assessmentDto)
    {
        $apiUrl = VehicleTestingStationUrlBuilder::validateSiteAssessment($siteId);
        $assessmentDto->setValidateOnly(true);

        return $this->post($apiUrl, DtoHydrator::dtoToJson($assessmentDto));
    }

    public function updateSiteAssessment($siteId, EnforcementSiteAssessmentDto $assessmentDto)
    {
        $apiUrl = VehicleTestingStationUrlBuilder::updateSiteAssessment($siteId);

        return $this->post($apiUrl, DtoHydrator::dtoToJson($assessmentDto));
    }

    /**
     * @param string
     *
     * @return VehicleTestingStationDto
     */
    public function getByNumber($vtsNumber)
    {
        $apiUrl = VehicleTestingStationUrlBuilder::vtsByNumber($vtsNumber);

        return DtoHydrator::jsonToDto($this->get($apiUrl));
    }
}
