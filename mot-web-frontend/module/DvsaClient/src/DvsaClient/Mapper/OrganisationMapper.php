<?php

namespace DvsaClient\Mapper;

use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\UrlBuilder\AuthorisedExaminerUrlBuilder;
use DvsaCommon\UrlBuilder\PersonUrlBuilder;
use DvsaCommon\Utility\DtoHydrator;

/**
 * Class OrganisationDtoMapper.
 */
class OrganisationMapper extends DtoMapper implements AutoWireableInterface
{
    /**
     * @param $managerId
     *
     * @return OrganisationDto[]
     */
    public function fetchAllForManager($managerId)
    {
        $url = PersonUrlBuilder::byId($managerId)->authorisedExaminer();

        return $this->get($url);
    }

    /**
     * @param $id
     *
     * @return OrganisationDto
     */
    public function getAuthorisedExaminer($id)
    {
        $url = AuthorisedExaminerUrlBuilder::of($id);

        return $this->get($url);
    }

    /**
     * @param array $params
     *
     * @return OrganisationDto
     */
    public function getAuthorisedExaminerByNumber($params)
    {
        $url = AuthorisedExaminerUrlBuilder::of()->authorisedExaminerByNumber();

        return $this->getWithParams($url, $params);
    }

    public function update($id, OrganisationDto $dto)
    {
        $url = AuthorisedExaminerUrlBuilder::of($id);

        return $this->put($url, DtoHydrator::dtoToJson($dto));
    }

    public function create(OrganisationDto $dto)
    {
        $url = AuthorisedExaminerUrlBuilder::of();

        return $this->post($url, DtoHydrator::dtoToJson($dto));
    }

    public function validate(OrganisationDto $dto)
    {
        $url = AuthorisedExaminerUrlBuilder::of();
        $dto->setIsValidateOnly(true);

        return $this->post($url, DtoHydrator::dtoToJson($dto));
    }

    /**
     * Answers a list of sites that are Area Offices. If the flag
     * 'for select' is true it means we want a K-V array instead of
     * the raw return data. This K-V list would be expected to be
     * used for SELECT content so we sort it by value.
     *
     * @param bool|false $forSelect
     *
     * @return Site[]
     */
    public function getAllAreaOffices($forSelect = false)
    {
        $url = AuthorisedExaminerUrlBuilder::getAllAreaOffices();
        $data = $this->get($url);

        if ($forSelect) {
            $areaOptions = [];
            foreach ($data as $ao) {
                $aoNumber = (int) $ao['areaOfficeNumber'];
                $areaOptions[$aoNumber] = $ao['areaOfficeNumber'];
            }

            return $areaOptions;
        }

        return $data;
    }

    /**
     * Updates given AE property.
     *
     * @param int    $aeId
     * @param string $property
     * @param mixed  $value
     *
     * @return mixed
     */
    public function updateAeProperty($aeId, $property, $value)
    {
        return $this->updateAePropertiesWithArray($aeId, [$property => $value]);
    }

    /**
     * Updates AE with array of values.
     *
     * @param int   $aeId
     * @param array $arrayOfValues
     *
     * @return mixed
     */
    public function updateAePropertiesWithArray($aeId, $arrayOfValues)
    {
        $apiUrl = AuthorisedExaminerUrlBuilder::of($aeId);

        return $this->patch($apiUrl, $arrayOfValues);
    }
}
