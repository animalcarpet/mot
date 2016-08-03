<?php

namespace DvsaClient\Mapper;

use DvsaClient\Entity\Person;
use DvsaCommon\Dto\Person\PersonDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\UrlBuilder\PersonUrlBuilder;
use DvsaCommon\UrlBuilder\UrlBuilder;
use DvsaCommon\Utility\DtoHydrator;

/**
 * Class PersonMapper
 *
 * @package DvsaClient\Mapper
 */
class PersonMapper extends AutoMapper implements AutoWireableInterface
{

    protected $entityClass = Person::class;

    /**
     * @param     $organisationId
     *
     * @return PersonDto[]
     */
    public function fetchPrincipalsForOrganisation($organisationId)
    {
        $url = UrlBuilder::authorisedExaminer()->routeParam('id', $organisationId)->authorisedExaminerPrincipal()
            ->toString();
        $persons = $this->client->get($url);

        $dtoHydrator = new DtoHydrator();

        $principals = $dtoHydrator->doHydration($persons['data']);
        return $principals;
    }

    /**
     * @param int $personId
     *
     * @return Person
     */
    public function getById($personId)
    {
        $url = UrlBuilder::person($personId)->toString();
        $person = $this->client->get($url);

        $obj = $this->doHydration($person['data']);
        return $obj;
    }

    /**
     * @param string $login
     *
     * @return Person
     */
    public function getByIdentifier($login)
    {
        $url = PersonUrlBuilder::byIdentifier($login)->toString();
        $person = $this->client->get($url);

        $obj = $this->doHydration($person['data']);
        return $obj;
    }

    public function createPrincipalsForOrganisation($organisationId, $data)
    {
        $url = UrlBuilder::authorisedExaminer()->routeParam('id', $organisationId)->authorisedExaminerPrincipal()
            ->toString();
        return $this->client->postJson($url, $data)['data']['authorisedExaminerPrincipalId'];
    }

    public function removePrincipalsForOrganisation($organisationId, $principalId)
    {
        $url = UrlBuilder::authorisedExaminer()->routeParam('id', $organisationId)->authorisedExaminerPrincipal()
            ->routeParam('principalId', $principalId)->toString();

        $this->client->delete($url);
    }
}
