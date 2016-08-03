<?php

namespace DvsaMotApiTest\Service\ReplacementCertificate;

use Api\Check\CheckMessage;
use Api\Check\CheckResult;
use DateTime;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Auth\MotIdentity;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApi\Service\Exception\ForbiddenException;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\ReplacementCertificateDraft;
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
    }

    public function testCreateGivenDraftShouldUpdateCertificate()
    {
        $this->userAssignedToVts();
        $this->userHasId(2);
        $this->permissionsGranted(
            [PermissionInSystem::CERTIFICATE_REPLACEMENT, PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS]
        );
        $draft = ReplacementCertificateObjectsFactory::replacementCertificateDraft()
            ->setReplacementReason("Reason");

//        when
        $motTest = $this->createSut()->update($draft);

        // then
        $this->assertEquals($draft->getPrimaryColour()->getId(), $motTest->getPrimaryColour()->getId());
        $this->assertEquals($draft->getSecondaryColour()->getId(), $motTest->getSecondaryColour()->getId());
        $this->assertEquals($draft->getExpiryDate(), $motTest->getExpiryDate());
        $this->assertEquals(
            $draft->getOdometerReading()->getValue(),
            $motTest->getOdometerReading()->getValue()
        );
        $this->assertEquals(
            $draft->getOdometerReading()->getUnit(),
            $motTest->getOdometerReading()->getUnit()
        );
        $this->assertEquals(
            $draft->getOdometerReading()->getResultType(),
            $motTest->getOdometerReading()->getResultType()
        );
    }

    public function testCreateGivenDraftShouldUpdateCertificateAndPrs()
    {
        $this->userAssignedToVts();
        $this->userHasId(2);
        $this->permissionsGranted(
            [PermissionInSystem::CERTIFICATE_REPLACEMENT, PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS]
        );

        $draft = ReplacementCertificateObjectsFactory::replacementCertificateDraft();

        $draft->setReplacementReason("Reason")
            ->setVin(rand(0,999999999999))
            ->setVrm(rand(0, 999999));
        $draft->getOdometerReading()->setValue(rand(0,999999));
        $draft->setExpiryDate(new DateTime());
        $draft->getMake()->setCode(rand(0,999999));
        $draft->getMotTest()->setPrsMotTest(MotTestObjectsFactory::motTest());
        $draft->getPrimaryColour()->setCode(rand(0, 100000));
        $draft->getSecondaryColour()->setCode(rand(0, 100000));
        $draft->getMake()->setCode(rand(0, 100000));
        $draft->getModel()->setCode(rand(0, 100000));

        $motTest = $this->createSut()->update($draft);

        $checkIfTestIsUpdatedFromDraft = function(ReplacementCertificateDraft $draft, MotTest $motTest){
            $this->assertEquals($draft->getOdometerReading(),$motTest->getOdometerReading());
            $this->assertEquals($draft->getPrimaryColour(), $motTest->getPrimaryColour());
            $this->assertEquals($draft->getSecondaryColour(), $motTest->getSecondaryColour());
            $this->assertEquals($draft->getModel(), $motTest->getModel());
            $this->assertEquals($draft->getMake(), $motTest->getMake());
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
        $draft->setReplacementReason(null);

        $this->createSut()->update($draft);
    }

    public function testCreateGivenDifferentTesterAndNoReasonForItShouldThrowForbiddenException()
    {
        $this->setExpectedException(ForbiddenException::class);
        $this->userAssignedToVts(false);
        $this->outsideOdometerReadingModificationWindow(false);
        $this->permissionsGranted([PermissionInSystem::CERTIFICATE_REPLACEMENT]);
        $draft = ReplacementCertificateObjectsFactory::replacementCertificateDraft();
        $draft->setReasonForDifferentTester(null);

        $this->createSut()->update($draft);
    }

    public function testIfMakeNameDefinedThenUnassociateMakeEntityAndSetMotTestMakeFreeText()
    {
        $this->userAssignedToVts();
        $this->userHasId(2);
        $this->permissionsGranted(
            [PermissionInSystem::CERTIFICATE_REPLACEMENT, PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS]
        );
        $draft = ReplacementCertificateObjectsFactory::replacementCertificateDraft()
            ->setReplacementReason("Reason")
            ->setMakeName('This is a make');

        $motTest = $this->createSut()->update($draft);

        $this->assertNull($motTest->getMake());
        $this->assertEquals('This is a make', $motTest->getMakeName());
    }

    public function testIfModelNameDefinedThenUnassociateModelEntityAndSetMotTestModelFreeText()
    {
        $this->userAssignedToVts();
        $this->userHasId(2);
        $this->permissionsGranted(
            [PermissionInSystem::CERTIFICATE_REPLACEMENT, PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS]
        );
        $draft = ReplacementCertificateObjectsFactory::replacementCertificateDraft()
            ->setReplacementReason("Reason")
            ->setModelName('This is a model');

        $motTest = $this->createSut()->update($draft);

        $this->assertNull($motTest->getModel());
        $this->assertEquals('This is a model', $motTest->getModelName());
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
