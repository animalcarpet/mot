<?php

namespace VehicleApi\Service\Mapper;

use DvsaCommon\Dto\Vehicle\CountryDto;
use DvsaCommonApi\Service\Mapper\AbstractApiMapper;
use DvsaEntities\Entity\CountryOfRegistration;

/**
 * Class CountryOfRegistrationMapper.
 */
class CountryOfRegistrationMapper extends AbstractApiMapper
{
    /**
     * @param CountryOfRegistration $countryOfReg
     *
     * @return CountryDto
     */
    public function toDto($countryOfReg)
    {
        $dto = new CountryDto();
        $dto->setCode($countryOfReg->getCode())
            ->setName($countryOfReg->getName())
            ->setLicensingCode($countryOfReg->getLicensingCopy())
            ->setId($countryOfReg->getId());

        return $dto;
    }
}
