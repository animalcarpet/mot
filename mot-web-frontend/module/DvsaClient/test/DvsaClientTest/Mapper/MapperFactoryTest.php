<?php

namespace DvsaClientTest\Mapper;

use DvsaClient\MapperFactory;
use DvsaCommon\HttpRestJson\Client;

/**
 * Class MapperFactoryTest.
 */
class MapperFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var MapperFactory */
    private $mapperFactory;

    public function setUp()
    {
        $client = \DvsaCommonTest\TestUtils\XMock::of(Client::class);

        $this->mapperFactory = new MapperFactory($client);
    }

    public function test_Person()
    {
        $this->assertInstanceOf(\DvsaClient\Mapper\PersonMapper::class, $this->mapperFactory->Person);
    }

    public function test_SitePosition()
    {
        $this->assertInstanceOf(\DvsaClient\Mapper\SitePositionMapper::class, $this->mapperFactory->SitePosition);
    }

    public function test_Equipment()
    {
        $this->assertInstanceOf(\DvsaClient\Mapper\EquipmentMapper::class, $this->mapperFactory->Equipment);
    }

    public function test_EquipmentModel()
    {
        $this->assertInstanceOf(\DvsaClient\Mapper\EquipmentModelMapper::class, $this->mapperFactory->EquipmentModel);
    }

    public function test_Organisation()
    {
        $this->assertInstanceOf(\DvsaClient\Mapper\OrganisationMapper::class, $this->mapperFactory->Organisation);
    }

    public function test_OrganisationPosition()
    {
        $this->assertInstanceOf(\DvsaClient\Mapper\OrganisationPositionMapper::class, $this->mapperFactory->OrganisationPosition);
    }

    public function test_OrganisationRole()
    {
        $this->assertInstanceOf(\DvsaClient\Mapper\OrganisationRoleMapper::CLASS_PATH, $this->mapperFactory->OrganisationRole);
    }

    public function test_Role()
    {
        $this->assertInstanceOf(\DvsaClient\Mapper\RoleMapper::class, $this->mapperFactory->Role);
    }

    public function test_SiteRole()
    {
        $this->assertInstanceOf(\DvsaClient\Mapper\SiteRoleMapper::class, $this->mapperFactory->SiteRole);
    }

    public function test_User()
    {
        $this->assertInstanceOf(\DvsaClient\Mapper\UserMapper::class, $this->mapperFactory->User);
    }

    public function test_VehicleTestingStation()
    {
        $this->assertInstanceOf(\DvsaClient\Mapper\SiteMapper::class, $this->mapperFactory->Site);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage  Class not found: DvsaClient\Mapper\NonExistingClassMapper
     */
    public function test_nonExistingMapper()
    {
        $this->mapperFactory->nonExistingClass;
    }
}
