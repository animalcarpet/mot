<?php

namespace OrganisationApi\Service\Mapper;

use DvsaCommon\Dto\AuthorisedExaminerPrincipal\AuthorisedExaminerPrincipalDto;
use DvsaCommonApi\Service\Mapper\AbstractApiMapper;
use DvsaEntities\Entity\AuthorisedExaminerPrincipal;
use DvsaCommon\Date\DateTimeApiFormat;

/**
 * Class AuthorisedExaminerPrincipalMapper.
 */
class AuthorisedExaminerPrincipalMapper extends AbstractApiMapper
{
    /**
     * @param AuthorisedExaminerPrincipal[] $principals
     *
     * @return AuthorisedExaminerPrincipalDto[]
     */
    public function manyToDto($principals)
    {
        return parent::manyToDto($principals);
    }

    /**
     * @param AuthorisedExaminerPrincipal $principal
     *
     * @return AuthorisedExaminerPrincipalDto
     */
    public function toDto($principal)
    {
        $aepDto = new AuthorisedExaminerPrincipalDto();
        $aepDto
            ->setId($principal->getId())
            ->setFirstName($principal->getFirstName())
            ->setMiddleName($principal->getMiddleName())
            ->setFamilyName($principal->getFamilyName())
            ->setDisplayName($principal->getDisplayName())
            ->setDateOfBirth(DateTimeApiFormat::date($principal->getDateOfBirth()));

        return $aepDto;
    }
}
