<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotApiTest\Service\ReplacementCertificate;

use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommonApi\Service\Exception\ForbiddenException;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\CountryOfRegistration;
use DvsaEntities\Entity\Make;
use DvsaEntities\Entity\Model;
use DvsaEntities\Entity\ModelDetail;
use DvsaEntities\Entity\MotTestStatus;
use DvsaEntities\Entity\Vehicle;
use DvsaMotApi\Service\MotTestSecurityService;
use DvsaMotApi\Service\ReplacementCertificate\ReplacementCertificateDraftCreator;
use DvsaMotApiTest\Factory\MotTestObjectsFactory;
use PHPUnit_Framework_TestCase;

/**
 * Class ReplacementCertificateDraftCreatorTest
 */
class ReplacementCertificateDraftCreatorTest extends PHPUnit_Framework_TestCase
{

    private $authService;
    private $motTestSecurityService;

    public function setUp()
    {
        $this->authService = XMock::of('DvsaAuthorisation\Service\AuthorisationServiceInterface', ['isGranted', 'getUserId']);
        $this->motTestSecurityService = XMock::of(MotTestSecurityService::class);
    }

    public function testCreateGivenMotTestNotBeingCertificateShouldThrowForbiddenException()
    {
        $this->setExpectedException(ForbiddenException::class);

        $motTest = MotTestObjectsFactory::motTest()->setStatus($this->createMotTestActiveStatus());
        $this->userAssignedToVts();
        $this->permissionsGranted(
            [PermissionInSystem::CERTIFICATE_REPLACEMENT, PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS]
        );

        $this->createSut()->create($motTest);
    }

    public function testCreateGivenNotAdminAndTesterNotAllowedToSeeMotTestShouldThrowForbiddenException()
    {
        $this->setExpectedException(ForbiddenException::class);

        $motTest = MotTestObjectsFactory::motTest();
        $this->userAssignedToVts(false);
        $this->permissionsGranted([PermissionInSystem::CERTIFICATE_REPLACEMENT]);

        $this->createSut()->create($motTest);
    }

    public function testCreateGivenMotTestShouldCreateValidDraft()
    {
        $make = (new Make())->setCode("BT")->setName("Bat");
        $model = (new Model())->setCode("MB")->setName("Mobil");
        $model->setMake($make);

        $modelDetail = new ModelDetail();
        $modelDetail->setModel($model);

        $vehicle = new Vehicle();
        $vehicle->setModelDetail($modelDetail);
        $vehicle->setCountryOfRegistration(new CountryOfRegistration());

        $motTest = MotTestObjectsFactory::motTest();
        $motTest->setVehicle($vehicle);
        $this->userAssignedToVts();
        $this->permissionsGranted(
            [PermissionInSystem::CERTIFICATE_REPLACEMENT, PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS]
        );

        $draft = $this->createSut()->create($motTest);

        $this->assertEquals($motTest->getVersion(), $draft->getMotTestVersion());
        $this->assertEquals($motTest->getExpiryDate(), $draft->getExpiryDate());
        $this->assertEquals($motTest->getOdometerValue(), $draft->getOdometerValue());
        $this->assertEquals($motTest->getOdometerUnit(), $draft->getOdometerUnit());
        $this->assertEquals($motTest->getOdometerResultType(), $draft->getOdometerResultType());
        $this->assertEquals($motTest->getMake(), $draft->getMake());
        $this->assertEquals($motTest->getModel(), $draft->getModel());
    }

    public function testCreatedGivenMotTestWithModelOtherAndMakeOtherShouldCreateValidDraft()
    {
        $make = (new Make())->setCode("BT")->setName("Bat");
        $model = (new Model())->setCode("MB")->setName("Mobil");
        $model->setMake($make);

        $modelDetail = new ModelDetail();
        $modelDetail->setModel($model);

        $vehicle = new Vehicle();
        $vehicle->setModelDetail($modelDetail);
        $vehicle->setCountryOfRegistration(new CountryOfRegistration());

        $motTest = MotTestObjectsFactory::motTest();
        $motTest->setVehicle($vehicle);
        $this->userAssignedToVts();
        $this->permissionsGranted(
            [PermissionInSystem::CERTIFICATE_REPLACEMENT, PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS]
        );

        $draft = $this->createSut()->create($motTest);

        $this->assertEquals($motTest->getModel(), $draft->getModel());
        $this->assertEquals($motTest->getModelName(), $draft->getModelName());
        $this->assertEquals($motTest->getMake(), $draft->getMake());
        $this->assertEquals($motTest->getMakeName(), $draft->getMakeName());
        $this->assertEquals($motTest->getVersion(), $draft->getMotTestVersion());
        $this->assertEquals($motTest->getExpiryDate(), $draft->getExpiryDate());
        $this->assertEquals($motTest->getOdometerValue(), $draft->getOdometerValue());
        $this->assertEquals($motTest->getOdometerUnit(), $draft->getOdometerUnit());
        $this->assertEquals($motTest->getOdometerResultType(), $draft->getOdometerResultType());
    }

    private function createSut()
    {
        return new ReplacementCertificateDraftCreator(
            $this->motTestSecurityService,
            $this->authService
        );
    }

    // TODO: move to a helper
    private function permissionsGranted($permissions)
    {
        $this->authService->expects($this->any())
            ->method("isGranted")
            ->will(
                $this->returnCallback(
                    function ($arg) use (&$permissions) {
                        return in_array($arg, $permissions);
                    }
                )
            );
    }

    private function userAssignedToVts($decision = true)
    {
        $this->motTestSecurityService->expects($this->any())
            ->method('isCurrentTesterAssignedToMotTest')
            ->will($this->returnValue($decision));
    }

    private function createMotTestActiveStatus()
    {
        $status = XMock::of(MotTestStatus::class);
        $status
            ->expects($this->any())
            ->method("getName")
            ->willReturn(MotTestStatusName::ACTIVE);

        return $status;
    }
}
