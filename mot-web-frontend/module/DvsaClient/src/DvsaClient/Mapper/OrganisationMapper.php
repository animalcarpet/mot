<?php

namespace DvsaClient\Mapper;

use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Dto\Organisation\SiteDto;
use DvsaCommon\UrlBuilder\AuthorisedExaminerUrlBuilder;
use DvsaCommon\UrlBuilder\PersonUrlBuilder;
use DvsaCommon\Utility\DtoHydrator;

/**
 * Class OrganisationDtoMapper
 *
 * @package DvsaClient\Mapper
 */
class OrganisationMapper extends DtoMapper
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

    public function status(OrganisationDto $dto, $id)
    {
        $url = AuthorisedExaminerUrlBuilder::status($id);

        return $this->put($url, DtoHydrator::dtoToJson($dto));
    }

    public function validateStatusAndAO(OrganisationDto $dto, $id)
    {
        $url = AuthorisedExaminerUrlBuilder::status($id);
        $dto->setIsValidateOnly(true);

        $data = DtoHydrator::dtoToJson($dto);

        return $this->put($url, $data);
    }

    /**
     * Answers a list of sites that are Area Offices. If the flag
     * 'for select' is true it means we want a K-V array instead of
     * the raw return data. This K-V list would be expected to be
     * used for SELECT content so we sort it by value.
     *
     * @param bool|false $forSelect
     * @return Site[]
     */
    public function getAllAreaOffices($forSelect = false)
    {
        $url = AuthorisedExaminerUrlBuilder::getAllAreaOffices();
        $data = $this->get($url);

        if ($forSelect) {
            $areaOptions = [];
            foreach($data as $ao) {
                $aoNumber = (int)$ao['areaOfficeNumber'];
                $areaOptions[$aoNumber] = $ao['areaOfficeNumber'];
            }
            return $areaOptions;
        }
        return $data;
    }
}
