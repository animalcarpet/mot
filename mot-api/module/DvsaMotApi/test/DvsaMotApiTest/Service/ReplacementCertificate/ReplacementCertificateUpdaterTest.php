<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotApiTest\Service\ReplacementCertificate;

use Api\Check\CheckMessage;
use Api\Check\CheckResult;
use DateTime;
use Dvsa\Mot\ApiClient\Resource\Item\Colour;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Resource\Item\Make as ApiMake;
use Dvsa\Mot\ApiClient\Resource\Item\Model as ApiModel;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Auth\MotIdentity;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApi\Service\Exception\ForbiddenException;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Make;
use DvsaEntities\Entity\Model;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\CertificateReplacementDraft;
use DvsaMotApi\Service\MotTestSecurityService;
use DvsaMotApi\Service\ReplacementCertificate\ReplacementCertificateUpdater;
use DvsaMotApiTest\Factory\MotTestObjectsFactory;
use DvsaMotApiTest\Factory\ReplacementCertificateObjectsFactory;
use PHPUnit_Framework_TestCase;

/**
 * Class ReplacementCertificateUpdaterTest
 */
class ReplacementCertificateUpdaterTest extends PHPUnit_Framework_TestCase
{

    private $authorizationService;
    private $motTestSecurityService;
    private $motIdentityService;
    private $mockVehicleService ;

    public function setUp()
    {
        $this->authorizationService = XMock::of('\DvsaAuthorisation\Service\AuthorisationServiceInterface', ['isGranted']);
        $this->motIdentityService = XMock::of('Zend\Authentication\AuthenticationService', ['getIdentity']);
        $this->motTestSecurityService = XMock::of(MotTestSecurityService::class);
        $this->mockVehicleService = XMock::of(VehicleService::class);

        $colour = new \stdClass();
        $colour->code = 'p';
        $colour = new Colour($colour);

        $make = new \stdClass();
        $make->name = 'makeName';
        $make = new ApiMake($make);

        $model = (new \stdClass());
        $model->name = 'makeName';
        $model = new ApiModel($model);

        $mockDvsaVehicle = XMock::of(DvsaVehicle::class);
        $mockDvsaVehicle->expects($this->any())->method('getVersion')->willReturn(1);
        $mockDvsaVehicle->expects($this->any())->method('getColour')->willReturn($colour);
        $mockDvsaVehicle->expects($this->any())->method('getColourSecondary')->willReturn($colour);
        $mockDvsaVehicle->expects($this->any())->method('getMake')->willReturn($make);
        $mockDvsaVehicle->expects($this->any())->method('getModel')->willReturn($model);

        $this->mockVehicleService->expects($this->any())
            ->method('updateDvsaVehicleAtVersion')
            ->willReturn($mockDvsaVehicle);

        $this->mockVehicleService->expects($this->any())
            ->method('getDvsaVehicleByIdAndVersion')
            ->willReturn($mockDvsaVehicle);


    }

    public function testCreateGivenDraftShouldUpdateCertificate()
    {
        $this->userAssignedToVts();
        $this->userHasId(2);
        $this->permissionsGranted(
            [PermissionInSystem::CERTIFICATE_REPLACEMENT, PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS]
        );
        $draft = ReplacementCertificateObjectsFactory::replacementCertificateDraft()
            ->setReasonForReplacement("Reason");

//        when
        $motTest = $this->createSut()->update($draft);

        // then
        $this->assertEquals($draft->getPrimaryColour()->getId(), $motTest->getPrimaryColour()->getId());
        $this->assertEquals($draft->getSecondaryColour()->getId(), $motTest->getSecondaryColour()->getId());
        $this->assertEquals($draft->getExpiryDate(), $motTest->getExpiryDate());
        $this->assertEquals($draft->getOdometerValue(), $motTest->getOdometerValue());
        $this->assertEquals($draft->getOdometerUnit(), $motTest->getOdometerUnit());
        $this->assertEquals($draft->getOdometerResultType(), $motTest->getOdometerResultType());
    }

    public function testCreateGivenDraftShouldUpdateCertificateAndPrs()
    {
        $this->userAssignedToVts();
        $this->userHasId(2);
        $this->permissionsGranted(
            [PermissionInSystem::CERTIFICATE_REPLACEMENT, PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS]
        );

        $draft = ReplacementCertificateObjectsFactory::replacementCertificateDraft();

        $draft->setReasonForReplacement("Reason")
            ->setVin(rand(0,999999999999))
            ->setVrm(rand(0, 999999));
        $draft->setOdometerValue(rand(0,999999));
        $draft->setExpiryDate(new DateTime());
        $draft->getMake()->setCode(rand(0,999999));
        $draft->getMotTest()->setPrsMotTest(MotTestObjectsFactory::motTest());

        $motTest = $this->createSut()->update($draft);

        $checkIfTestIsUpdatedFromDraft = function(CertificateReplacementDraft $draft, MotTest $motTest){
            $this->assertEquals($draft->getOdometerValue(),$motTest->getOdometerValue());
        };

        $checkIfTestIsUpdatedFromDraft($draft, $motTest);
        $checkIfTestIsUpdatedFromDraft($draft, $motTest->getPrsMotTest());
        $this->assertEquals($draft->getExpiryDate(), $motTest->getExpiryDate());
        $this->assertNotEquals($draft->getExpiryDate(), $motTest->getPrsMotTest()->getExpiryDate());

    }

    public function testCreateGivenDifferentMotTestVersionWhenApplyingShouldThrowForbiddenException()
    {
        $this->setExpectedException(ForbiddenException::class);
        $this->userAssignedToVts();
        $this->permissionsGranted(
            [PermissionInSystem::CERTIFICATE_REPLACEMENT, PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS]
        );
        $draft = ReplacementCertificateObjectsFactory::replacementCertificateDraft();
        $draft->getMotTest();
        $draft->setMotTestVersion(2);
        $this->createSut()->update($draft);
    }

    public function testCreateGivenDifferentUserWhenApplyingShouldThrowForbiddenException()
    {
        $this->setExpectedException(ForbiddenException::class);
        $this->userHasId(4);
        $this->userAssignedToVts();
        $this->permissionsGranted(
            [PermissionInSystem::CERTIFICATE_REPLACEMENT, PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS]
        );
        $draft = ReplacementCertificateObjectsFactory::replacementCertificateDraft();
        $this->createSut()->update($draft);
    }

    public function testCreateGivenPartialRightsAndChangeIsOutsideModificationPeriodShouldThrowBadRequestException()
    {
        $this->setExpectedException(BadRequestException::class);
        $this->userAssignedToVts();
        $this->outsideOdometerReadingModificationWindow();
        $this->permissionsGranted([PermissionInSystem::CERTIFICATE_REPLACEMENT]);
        $draft = ReplacementCertificateObjectsFactory::replacementCertificateDraft();

        $this->createSut()->update($draft);
    }

    public function testCreateGivenNoReasonForReplacementShouldThrowForbiddenException()
    {
        $this->setExpectedException(ForbiddenException::class);
        $this->userAssignedToVts();
        $this->outsideOdometerReadingModificationWindow(false);
        $this->permissionsGranted(
            [PermissionInSystem::CERTIFICATE_REPLACEMENT, PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS]
        );
        $draft = ReplacementCertificateObjectsFactory::replacementCertificateDraft();
        $draft->setReasonForReplacement(null);

        $this->createSut()->update($draft);
    }

    public function testCreateGivenDifferentTesterAndNoReasonForItShouldThrowForbiddenException()
    {
        $this->setExpectedException(ForbiddenException::class);
        $this->userAssignedToVts(false);
        $this->outsideOdometerReadingModificationWindow(false);
        $this->permissionsGranted([PermissionInSystem::CERTIFICATE_REPLACEMENT]);
        $draft = ReplacementCertificateObjectsFactory::replacementCertificateDraft();
        $draft->setDifferentTesterReason(null);

        $this->createSut()->update($draft);
    }

    private function createSut()
    {
        return new ReplacementCertificateUpdater(
            $this->motTestSecurityService,
            $this->authorizationService,
            $this->motIdentityService,
            $this->mockVehicleService
        );
    }

    // TODO: move to a helper
    private function permissionsGranted($permissions)
    {
        $this->authorizationService->expects($this->any())
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

    private function outsideOdometerReadingModificationWindow($decision = true)
    {
        $this->motTestSecurityService->expects($this->any())
            ->method('validateOdometerReadingModificationWindowOpen')
            ->will($this->returnValue($decision ? CheckResult::with(CheckMessage::withError()) : CheckResult::ok()));
    }

    private function userHasId($id = null)
    {
        $this->motIdentityService->expects($this->any())->method("getIdentity")
            ->will($this->returnValue(new MotIdentity($id, null)));
    }
}
