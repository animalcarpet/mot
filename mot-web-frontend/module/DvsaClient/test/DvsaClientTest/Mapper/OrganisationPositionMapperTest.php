<?php

namespace DvsaClientTest\Mapper;

use DvsaClient\Mapper\OrganisationPositionMapper;
use DvsaCommon\Dto\Organisation\OrganisationPositionDto;
use DvsaCommon\UrlBuilder\OrganisationUrlBuilder;
use DvsaCommon\Utility\DtoHydrator;

class OrganisationPositionMapperTest extends AbstractMapperTest
{
    const ORG_ID = 99999;
    const POSTION_ID = 7777;

    /** @var OrganisationPositionMapper $mapper */
    private $mapper;

    public function setUp()
    {
        parent::setUp();

        $this->mapper = new OrganisationPositionMapper($this->client);
    }

    public function testFetchAllPositionsForOrganisation()
    {
        $expectDto = (new OrganisationPositionDto())
            ->setId(7777);

        $this->setupClientMockGet(
            OrganisationUrlBuilder::position(self::ORG_ID),
            ['data' => [DtoHydrator::dtoToJson($expectDto)]]
        );
        $actual = $this->mapper->fetchAllPositionsForOrganisation(self::ORG_ID);

        $this->assertEquals([$expectDto], $actual);
    }

    public function testCreatePosition()
    {
        $nomineeId = 999;
        $roleId    = 888;

        $expect = 'expectResult';

        $this->setupClientMockPost(
            OrganisationUrlBuilder::position(self::ORG_ID),
            [
                'nomineeId' => $nomineeId,
                'roleId'    => $roleId
            ],
            ['data' => $expect]
        );

        $actual = $this->mapper->createPosition(self::ORG_ID, $nomineeId, $roleId);

        $this->assertEquals($expect, $actual);
    }

    public function testUpdatePosition()
    {
        $nomineeId = 999;
        $roleId    = 888;

        $expect = 'expectResult';

        $this->setupClientMockPut(
            OrganisationUrlBuilder::position(self::ORG_ID),
            [
                'nomineeId' => $nomineeId,
                'roleId'    => $roleId
            ],
            ['data' => $expect]
        );

        $actual = $this->mapper->updatePosition(self::ORG_ID, $nomineeId, $roleId);

        $this->assertEquals($expect, $actual);
    }

    public function testDelete()
    {
        $this->setupClientMockDelete(
            OrganisationUrlBuilder::position(self::ORG_ID, self::POSTION_ID),
            ['data' => true]
        );

        $this->mapper->deletePosition(self::ORG_ID, self::POSTION_ID);
    }
}
