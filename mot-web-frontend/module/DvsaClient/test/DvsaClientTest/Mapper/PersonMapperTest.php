<?php

namespace DvsaClientTest\Mapper;

use DvsaClient\Mapper\PersonMapper;
use DvsaCommon\UrlBuilder\PersonUrlBuilder;

/**
 * Class PersonMapperTest.
 */
class PersonMapperTest extends AbstractMapperTest
{
    /** @var $mapper PersonMapper */
    private $mapper;

    public function setUp()
    {
        parent::setUp();

        $this->mapper = new PersonMapper($this->client);
    }

    public function testFetchPrincipalsForOrganisation()
    {
        $this->mapper->fetchPrincipalsForOrganisation(1);
    }

    public function testGetById()
    {
        $this->setupClientMockGet(
            PersonUrlBuilder::byId(1)->toString(),
            ['data' => []]
        );
        $this->mapper->getById(1);
    }

    public function testGetByIdentifier()
    {
        $this->setupClientMockGet(
            PersonUrlBuilder::byIdentifier('aaa')->toString(),
            ['data' => []]
        );
        $this->mapper->getByIdentifier('aaa');
    }

    public function testCreatePrincipal()
    {
        $this->mapper->createPrincipalsForOrganisation(1, []);
    }

    public function testRemovePrincipalsForOrganisation()
    {
        $this->mapper->removePrincipalsForOrganisation(1, 1);
    }
}
