<?php

namespace DvsaMotApiTest\Service;

use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use DvsaAuthentication\IdentityProvider;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaAuthorisation\Service\RoleProviderService;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\MotTestRepository;
use DvsaEntities\Repository\PersonRepository;
use DvsaEntities\Repository\SiteRepository;
use DvsaMotApi\Service\TesterService;
use PHPUnit_Framework_TestCase;
use UserApi\SpecialNotice\Service\SpecialNoticeService;

class TesterServiceTest extends PHPUnit_Framework_TestCase
{
    private $motTestRepository;
    private $authService;

    public function setUp()
    {
        $this->motTestRepository = XMock::of(MotTestRepository::class);
        $this->authService = XMock::of(AuthorisationServiceInterface::class);
    }

    public function testNonMotTestNumberReturnedIfUserHasPermission()
    {
        $nonMotNumber = 'MOT123456';

        $service = $this
            ->withNonMotPermission()
            ->withNonMotNumber($nonMotNumber)
            ->buildService();

        $this->assertEquals($nonMotNumber, $service->findInProgressNonMotTestNumberForVehicleExaminer(1));
    }

    public function testNonMotTestNumberNotReturnedIfUserDoesNotHavePermission()
    {
        $this->setExpectedException(UnauthorisedException::class);

        $service = $this->withoutNonMotPermission()->buildService();

        $service->findInProgressNonMotTestNumberForVehicleExaminer(1);
    }

    private function withNonMotPermission()
    {
        $this->authService
            ->expects($this->any())
            ->method('isGranted')
            ->willReturn(true);

        return $this;
    }

    private function withoutNonMotPermission()
    {
        $this->authService
            ->expects($this->any())
            ->method('isGranted')
            ->willReturn(false);

        return $this;
    }

    private function withNonMotNumber($number)
    {
        $this->motTestRepository
            ->expects($this->any())
            ->method('findInProgressNonMotTestNumberForPerson')
            ->willReturn($number);

        return $this;
    }

    private function buildService()
    {
        $entityManager = XMock::of(EntityManager::class);
        $entityManager
            ->expects($this->any())
            ->method('getRepository')
            ->willReturnCallback(function ($className) {
                if ($className == Person::class) {
                    return XMock::of(PersonRepository::class);
                } elseif ($className == MotTest::class) {
                    return $this->motTestRepository;
                } else {
                    return null;
                }
            });

        return new TesterService(
            $entityManager,
            XMock::of(DoctrineObject::class),
            $this->authService,
            XMock::of(SpecialNoticeService::class),
            XMock::of(RoleProviderService::class),
            XMock::of(IdentityProvider::class),
            XMock::of(SiteRepository::class)
        );
    }
}
