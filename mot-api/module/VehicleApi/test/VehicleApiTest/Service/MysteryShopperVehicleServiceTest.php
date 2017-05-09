<?php

namespace VehicleApiTest\Service;

use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentityInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\IncognitoVehicle;
use DvsaEntities\Entity\Vehicle;
use DvsaEntities\Repository\PersonRepository;
use DvsaEntities\Repository\SiteRepository;
use DvsaEntities\Repository\VehicleRepository;
use VehicleApi\InputFilter\MysteryShopperInputFilter;
use VehicleApi\Service\MysteryShopperVehicleService;
use DvsaEntities\Repository\IncognitoVehicleRepository;

class MysteryShopperVehicleServiceTest extends AbstractServiceTestCase
{
    /**
     * @var MysteryShopperVehicleService
     */
    private $subject;

    private $mockSiteRepository;
    private $mockIncognitoVehicleRepository;
    private $mockMysteryShopperInputFilter;
    private $mockVehicleRepository;
    private $mockPersonRepository;

    public function setUp()
    {
        /** @var AuthorisationServiceInterface $mockAuthService */
        $mockAuthService = XMock::of(AuthorisationServiceInterface::class, ['isGranted', 'assertGranted']);

        /** @var MotIdentityProviderInterface $mockIdentityProvider */
        $mockIdentityProvider = XMock::of(MotIdentityProviderInterface::class);
        $mockIdentityProvider
            ->expects($this->any())
            ->method('getIdentity')
            ->willReturn(XMock::of(MotIdentityInterface::class));

        /* @var SiteRepository $mockSiteRepository */
        $this->mockSiteRepository = XMock::of(SiteRepository::class);

        /* @var IncognitoVehicleRepository $mockIncognitoVehicleRepository */
        $this->mockIncognitoVehicleRepository = XMock::of(IncognitoVehicleRepository::class);

        /* @var MysteryShopperInputFilter $mockMysteryShopperInputFilter */
        $this->mockMysteryShopperInputFilter = XMock::of(MysteryShopperInputFilter::class);

        /* @var VehicleRepository $mockVehicleRepository */
        $this->mockVehicleRepository = XMock::of(VehicleRepository::class);

        /* @var PersonRepository $mockPersonRepository */
        $this->mockPersonRepository = XMock::of(PersonRepository::class);

        /* @var MysteryShopperVehicleService subject */
        $this->subject = new MysteryShopperVehicleService(
            $mockAuthService,
            $mockIdentityProvider,
            $this->mockMysteryShopperInputFilter,
            $this->mockSiteRepository,
            $this->mockIncognitoVehicleRepository,
            $this->mockVehicleRepository,
            $this->mockPersonRepository
        );
    }

    public function testOptOut()
    {
        $id = 1;

        $this->mockIncognitoVehicleRepository->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn($this->mockIncognitoVehicleEnity());

        $actual = $this->subject->optOut($id);
        $this->assertTrue($actual);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function testOptOutNotFound()
    {
        $id = 1;

        $this->mockIncognitoVehicleRepository->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn(null);

        $this->subject->optOut($id);
    }

    public function testCurrentWithCurrentCampaign()
    {
        $id = 1;

        $this->mockVehicleRepository->expects($this->once())
            ->method('get')
            ->with($id)
            ->willReturn($this->mockVehicleEntity());

        $expected = $this->mockIncognitoVehicleEnity();

        $this->mockIncognitoVehicleRepository->expects($this->once())
            ->method('getCurrent')
            ->willReturn($expected);

        $actual = $this->subject->getCurrent($id);
        $this->assertInstanceOf(IncognitoVehicle::class, $actual);
        $this->assertSame($expected, $actual);
    }

    public function testCurrentNoActiveCampaign()
    {
        $id = 1;

        $this->mockVehicleRepository->expects($this->once())
            ->method('get')
            ->with($id)
            ->willReturn($this->mockVehicleEntity());

        $this->mockIncognitoVehicleRepository->expects($this->once())
            ->method('getCurrent')
            ->willReturn(null);

        $actual = $this->subject->getCurrent($id);
        $this->assertNull($actual);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function testCurrentVehicleNotFound()
    {
        $id = 1;

        $this->mockVehicleRepository->expects($this->once())
            ->method('get')
            ->with($id)
            ->willReturn(null);

        $this->subject->getCurrent($id);
    }

    private function mockVehicleEntity()
    {
        return new Vehicle();
    }

    private function mockIncognitoVehicleEnity()
    {
        return new IncognitoVehicle();
    }
}
