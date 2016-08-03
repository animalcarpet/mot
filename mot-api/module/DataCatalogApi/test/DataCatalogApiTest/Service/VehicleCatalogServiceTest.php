<?php

namespace DataCatalogApiTest\Service;

use DataCatalogApi\Service\VehicleCatalogService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use DvsaCommonTest\TestUtils\MultiCallStubBuilder;
use DvsaCommonTest\TestUtils\NumbProbe;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Colour;
use DvsaEntities\Entity\CountryOfRegistration;
use DvsaEntities\Entity\DvlaMakeModelMap;
use DvsaEntities\Entity\FuelType;
use DvsaEntities\Entity\Make;
use DvsaEntities\Entity\Model;
use DvsaEntities\Entity\ModelDetail;
use DvsaEntities\Entity\TransmissionType;

/**
 * Class VehicleCatalogServiceTest
 *
 * @package DataCatalogApiTest\Service
 */
class VehicleCatalogServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \DataCatalogApi\Service\VehicleCatalogService */
    private $vcs;
    private $em;

    public function setUp()
    {
        $this->em = XMock::of(EntityManager::class);
        $this->em->expects($this->any())->method('getRepository')
            ->will(
                MultiCallStubBuilder::of()
                    ->add(Make::class, new NumbProbe())
                    ->add(Model::class, new NumbProbe())
                    ->add(ModelDetail::class, new NumbProbe())
                    ->add(FuelType::class, new NumbProbe())
                    ->add(CountryOfRegistration::class, new NumbProbe())
                    ->add(Colour::class, new NumbProbe())
                    ->add(TransmissionType::class, new NumbProbe())
                    ->add(DvlaMakeModelMap::class, new NumbProbe())
                    ->build()
            );
        $this->vcs = new VehicleCatalogService($this->em);
    }

    public function testGetModelById()
    {
        $this->vcs->getModelById("id");
    }

    public function testGetMake()
    {
        $this->vcs->getMake("code");
    }

    public function testGetMakes()
    {
        $this->vcs->getMakes();
    }

    public function testFindMakeByName()
    {
        $this->vcs->findMakeByName("name");
    }

    public function testFindModelByName()
    {
        $this->vcs->findModelByNameAndCodeId("name", "make");
    }

    public function testModelsByMake()
    {
        $this->vcs->getModelsByMake("make");
    }

    public function testGetModel()
    {
        $this->vcs->getModel("make", "model");
    }

    public function testGetModelDetail()
    {
        $this->vcs->getModelDetail('1');
    }

    public function testGetModelDetail_refOnly()
    {
        $this->vcs->getModelDetail('1', true);
    }

    public function testGetModelDetailsByModel()
    {
        $this->vcs->getModelDetailsByModel('make', 'model');
    }

    public function testGetFuelType_refOnly()
    {
        $this->vcs->getFuelType(1, true);
    }

    public function testGetFuelType()
    {
        $this->vcs->getFuelType(1);
    }

    public function testFindFuelTypeByPropulsionCode()
    {
        $this->vcs->findFuelTypeByPropulsionCode('1');
    }

    public function testGetCountryOfRegistration()
    {
        $this->vcs->getCountryOfRegistration("12");
    }

    public function testGetCountryOfRegistration_refOnly()
    {
        $this->vcs->getCountryOfRegistration("12", true);
    }

    public function testGetCountryOfRegistrationByCode()
    {
        $this->vcs->getCountryOfRegistrationByCode("UK");
    }

    public function testGetColour()
    {
        $this->vcs->getColour("12");
    }

    public function testGetColour_refOnly()
    {
        $this->vcs->getColour("12", true);
    }

    public function testGetColourByCode()
    {
        $this->vcs->getColourByCode("R");
    }

    public function testFindColourByCode()
    {
        $this->vcs->findColourByCode("R");
    }

    public function testGetTransmissionType()
    {
        $this->vcs->getTransmissionType("12");
    }

    public function testGetTranmissionType_refOnly()
    {
        $this->vcs->getTransmissionType("12", true);
    }

    public function testGetMakeModelMapByDvlaCode()
    {
        $make                   = $this->getMock(Make::class);
        $model                  = $this->getMock(Model::class);

        $dvlaMakeModelMapEntity = $this->getMock(DvlaMakeModelMap::class);
        $dvlaMakeModelMapEntity
            ->expects($this->once())
            ->method('getMake')
            ->will($this->returnValue($make));

        $dvlaMakeModelMapEntity
            ->expects($this->once())
            ->method('getModel')
            ->will($this->returnValue($model));

        $dvlaMakeModelMapRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dvlaMakeModelMapRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->will($this->returnValue($dvlaMakeModelMapEntity));

        $entityManager = $this
            ->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($dvlaMakeModelMapRepository));

        $vcs = new VehicleCatalogService($entityManager);
        $map = $vcs->getMakeModelMapByDvlaCode('AA', '000');
        $this->assertEquals($dvlaMakeModelMapEntity, $map);
        $this->assertEquals($make, $map->getMake());
        $this->assertEquals($model, $map->getModel());
    }

}
