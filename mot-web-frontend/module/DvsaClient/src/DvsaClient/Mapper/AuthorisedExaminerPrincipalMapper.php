<?php

namespace DvsaClient\Mapper;

use DvsaCommon\Dto\AuthorisedExaminerPrincipal\AuthorisedExaminerPrincipalDto;
use DvsaCommon\UrlBuilder\AuthorisedExaminerUrlBuilder;
use DvsaCommon\Utility\DtoHydrator;
use DvsaClient\Entity\AuthorisedExaminerPrincipal;

/**
 * Class AuthorisedExaminerPrincipalDtoMapper.
 */
class AuthorisedExaminerPrincipalMapper extends AutoMapper
{
    protected $entityClass = AuthorisedExaminerPrincipal::class;

    /**
     * @param   $organisationId
     *
     * @return AuthorisedExaminerPrincipalDto[]
     */
    public function fetchPrincipalsForOrganisation($organisationId)
    {
        $url = AuthorisedExaminerUrlBuilder::of($organisationId)->authorisedExaminerPrincipal()->toString();
        $principals = $this->client->get($url);

        $dtoHydrator = new DtoHydrator();

        $obj = $dtoHydrator->doHydration($principals['data']);

        return $obj;
    }

    /**
     * @param string $id
     *
     * @return AuthorisedExaminerPrincipal
     */
    public function getByIdentifier($organisationId, $principalId)
    {
        $url = AuthorisedExaminerUrlBuilder::of($organisationId)->authorisedExaminerPrincipal()
            ->routeParam('principalId', $principalId)->toString();
        $principal = $this->client->get($url);

        $dtoHydrator = new DtoHydrator();
        $obj = $dtoHydrator->doHydration($principal['data']);

        return $obj;
    }

    public function removePrincipalsForOrganisation($organisationId, $principalId)
    {
        $url = AuthorisedExaminerUrlBuilder::of($organisationId)->authorisedExaminerPrincipal()
            ->routeParam('principalId', $principalId)->toString();

        $this->client->delete($url);
    }
}
