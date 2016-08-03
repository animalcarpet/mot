<?php

namespace DvsaMotApiTest\Service\ReplacementCertificate;

use DataCatalogApi\Service\VehicleCatalogService;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Auth\PermissionInSystem;
use Api\Check\CheckResult;
use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommonApi\Service\Exception\ForbiddenException;
use DvsaCommonTest\TestUtils\MultiCallStubBuilder;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\CertificateChangeDifferentTesterReason;
use DvsaEntities\Entity\Colour;
use DvsaEntities\Entity\CountryOfRegistration;
use DvsaEntities\Entity\Make;
use DvsaEntities\Entity\Model;
use DvsaEntities\Entity\Site;
use DvsaEntities\Repository\CertificateChangeReasonRepository;
use DvsaEntities\Repository\SiteRepository;
use DvsaMotApi\Helper\Odometer\OdometerHolderUpdater;
use DvsaMotApi\Service\MotTestSecurityService;
use DvsaMotApi\Service\ReplacementCertificate\ReplacementCertificateDraftUpdater;
use DvsaMotApi\Service\Validator\ReplacementCertificateDraftChangeValidator;
use DvsaMotApiTest\Factory\MotTestObjectsFactory;
use DvsaMotApiTest\Factory\ReplacementCertificateObjectsFactory as RCOF;
use DvsaMotApiTest\Factory\VehicleObjectsFactory;
use PHPUnit_Framework_TestCase;
use DvsaCommonApiTest\Transaction\TestTransactionExecutor;
use DvsaCommon\Date\DateUtils;

/**
 * Test for {@link ReplacementCertificateDraftUpdater}
 */
class ReplacementCertificateDraftUpdaterTest extends PHPUnit_Framework_TestCase
{
    const ID_SEED = 21;

    private $motTestSecurityService;
    private $authorizationService;
    private $vehDict;
    private $vtsRepository;
    private $certificateChangeReasonRepository;

    /** @var  MotIdentityProviderInterface $motIdentityProvider */
    private $motIdentityProvider;
    private $replacementCertificateDraftChangeValidator;
    private $odometerHolderUpdater;

    public function setUp()
    {
        $this->motTestSecurityService = XMock::of(MotTestSecurityService::class);
        $this->authorizationService = XMock::of('DvsaAuthorisation\Service\AuthorisationServiceInterface', ['isGranted']);
        $this->vehDict = XMock::of(VehicleCatalogService::class);
        $this->vtsRepository = XMock::of(SiteRepository::class);
        $this->certificateChangeReasonRepository = XMock::of(CertificateChangeReasonRepository::class);
        $this->motIdentityProvider = XMock::of(\Zend\Authentication\AuthenticationService::class);
        $this->replacementCertificateDraftChangeValidator = XMock::of(
            ReplacementCertificateDraftChangeValidator::class
        );
        $this->odometerHolderUpdater = XMock::of(OdometerHolderUpdater::class);

        $replacementChange = RCOF::fullReplacementCertificateDraftChange(self::ID_SEED);

        $this->authorizationService->expects($this->any())->method("getUserId")
            ->will($this->returnValue(null));

        $this->returnsMake(VehicleObjectsFactory::make($replacementChange->getMake()));
        $this->returnsMakeByCode(VehicleObjectsFactory::make($replacementChange->getMake()));
        $this->returnsModel(VehicleObjectsFactory::model($replacementChange->getModel()));
        $this->returnsVts(MotTestObjectsFactory::vts(999, $replacementChange->getVtsSiteNumber()));
        $this->returnsColours(
            (new Colour())->setCode($replacementChange->getPrimaryColour()),
            (new Colour())->setCode($replacementChange->getSecondaryColour())
        );
        $this->returnsCountryOfRegistration(
            VehicleObjectsFactory::countryOfRegistration($replacementChange->getCountryOfRegistration())
        );
    }

    public function testSetIncludeInMismatchFile_isAlwaysFalse_forDVLAChangePermission_Only_VRM_Changed()
    {
        //given
        $this->returnsOkCheckResult();

        $this->permissionsGranted(
            [
                PermissionInSystem::CERTIFICATE_REPLACEMENT_DVLA_CHANGE,
                PermissionInSystem::CERTIFICATE_REPLACEMENT,
                PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS
            ]
        );

        $vin = '123456';
        $vrm = '123456';

        $draft = RCOF::replacementCertificateDraft()->setReplacementReason("REASON");
        $draft->getMotTest()->setVin($vin);
        $draft->setVin($vin);
        $draft->getMotTest()->setRegistration($vrm);
        $draft->setVrm($vrm);

        $change = RCOF::fullReplacementCertificateDraftChange(self::ID_SEED)->setReasonForReplacement("NEW_REASON");

        $change->setVin($vin);
        $change->setVrm('123457'); // vrm change

        //when
        $updatedDraft = $this->createSUT()->updateDraft($draft, $change);

        //then
        $this->assertFalse($updatedDraft->includeInMismatchFile());
    }

    public function testSetIncludeInPassFile_isAlwaysTrue_forDVLAChangePermission_Only_Vin_Changed()
    {
        //given
        $this->returnsOkCheckResult();

        $this->permissionsGranted(
            [
                PermissionInSystem::CERTIFICATE_REPLACEMENT_DVLA_CHANGE,
                PermissionInSystem::CERTIFICATE_REPLACEMENT,
                PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS
            ]
        );

        $vin = '123456';
        $vrm = '123456';

        $draft = RCOF::replacementCertificateDraft()->setReplacementReason("REASON");
        $draft->getMotTest()->setVin($vin);
        $draft->setVin($vin);
        $draft->getMotTest()->setRegistration($vrm);
        $draft->setVrm($vrm);

        $change = RCOF::fullReplacementCertificateDraftChange(self::ID_SEED)->setReasonForReplacement("NEW_REASON");

        $change->setVin('654321'); // vin change
        $change->setVrm($vrm);

        //when
        $updatedDraft = $this->createSUT()->updateDraft($draft, $change);

        //then
        $this->assertTrue($updatedDraft->includeInPassFile());
    }

    public function testSetIncludeInMismatchFile_isAlwaysFalse_forDVLAChangePermission_Only_Vin_Changed()
    {
        //given
        $this->returnsOkCheckResult();

        $this->permissionsGranted(
            [
                PermissionInSystem::CERTIFICATE_REPLACEMENT_DVLA_CHANGE,
                PermissionInSystem::CERTIFICATE_REPLACEMENT,
                PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS
            ]
        );

        $vin = '123456';
        $vrm = '123456';

        $draft = RCOF::replacementCertificateDraft()->setReplacementReason("REASON");
        $draft->getMotTest()->setVin($vin);
        $draft->setVin($vin);
        $draft->getMotTest()->setRegistration($vrm);
        $draft->setVrm($vrm);

        $change = RCOF::fullReplacementCertificateDraftChange(self::ID_SEED)->setReasonForReplacement("NEW_REASON");

        $change->setVin('654321'); // vin change
        $change->setVrm($vrm);

        //when
        $updatedDraft = $this->createSUT()->updateDraft($draft, $change);

        //then
        $this->assertFalse($updatedDraft->includeInMismatchFile());
    }

    public function testSetIncludeInPassFile_isAlwaysTrue_forDVLAChangePermission_Only_Vrm_Changed()
    {
        //given
        $this->returnsOkCheckResult();

        $this->permissionsGranted(
            [
                PermissionInSystem::CERTIFICATE_REPLACEMENT_DVLA_CHANGE,
                PermissionInSystem::CERTIFICATE_REPLACEMENT,
                PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS
            ]
        );

        $vin = '123456';
        $vrm = '123456';

        $draft = RCOF::replacementCertificateDraft()->setReplacementReason("REASON");
        $draft->getMotTest()->setVin($vin);
        $draft->setVin($vin);
        $draft->getMotTest()->setRegistration($vrm);
        $draft->setVrm($vrm);

        $change = RCOF::fullReplacementCertificateDraftChange(self::ID_SEED)->setReasonForReplacement("NEW_REASON");

        $change->setVin($vin);
        $change->setVrm('123457'); // vrm change

        //when
        $updatedDraft = $this->createSUT()->updateDraft($draft, $change);

        //then
        $this->assertTrue($updatedDraft->includeInPassFile());
    }

    public function testSetIncludeInMismatchFile_isAlwaysFalse_forDVLAChangePermission_Only_Make_Changed()
    {
        //given
        $this->returnsOkCheckResult();

        $this->permissionsGranted(
            [
                PermissionInSystem::CERTIFICATE_REPLACEMENT_DVLA_CHANGE,
                PermissionInSystem::CERTIFICATE_REPLACEMENT,
                PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS
            ]
        );

        $vin = '123456';
        $vrm = '123456';

        $draft = RCOF::replacementCertificateDraft()->setReplacementReason("REASON");
        $draft->getMotTest()->setVin($vin);
        $draft->setVin($vin);
        $draft->getMotTest()->setRegistration($vrm);
        $draft->setVrm($vrm);

        $change = RCOF::fullReplacementCertificateDraftChange(self::ID_SEED)
            ->setMake("18800")
            ->setReasonForReplacement("NEW_REASON");

        $change->setVin($vin);
        $change->setVrm($vrm); // vrm change

        //when
        $updatedDraft = $this->createSUT()->updateDraft($draft, $change);

        //then
        $this->assertFalse($updatedDraft->includeInMismatchFile());
    }

    public function testSetIncludeInPassFile_isAlwaysFalse_forDVLAChangePermission_Only_Make_Changed()
    {
        //given
        $this->returnsOkCheckResult();

        $this->permissionsGranted(
            [
                PermissionInSystem::CERTIFICATE_REPLACEMENT_DVLA_CHANGE,
                PermissionInSystem::CERTIFICATE_REPLACEMENT,
                PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS
            ]
        );

        $vin = '123456';
        $vrm = '123456';

        $draft = RCOF::replacementCertificateDraft()->setReplacementReason("REASON");
        $draft->getMotTest()->setVin($vin);
        $draft->setVin($vin);
        $draft->getMotTest()->setRegistration($vrm);
        $draft->setVrm($vrm);

        $change = RCOF::fullReplacementCertificateDraftChange(self::ID_SEED)
            ->setMake("18800")
            ->setReasonForReplacement("NEW_REASON");

        $change->setVin($vin);
        $change->setVrm($vrm);

        //when
        $updatedDraft = $this->createSUT()->updateDraft($draft, $change);

        //then
        $this->assertFalse($updatedDraft->includeInPassFile());
    }

    public function testSetIncludeInMismatchFile_isAlwaysTrue_forDVSAuser_Only_VRM_Changed()
    {
        //given
        $this->returnsOkCheckResult();

        $this->permissionsGranted(
            [
                PermissionInSystem::CERTIFICATE_REPLACEMENT,
                PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS
            ]
        );

        $vin = '123456';
        $vrm = '123456';

        $draft = RCOF::replacementCertificateDraft()->setReplacementReason("REASON");
        $draft->getMotTest()->setVin($vin);
        $draft->setVin($vin);
        $draft->getMotTest()->setRegistration($vrm);
        $draft->setVrm($vrm);

        $change = RCOF::fullReplacementCertificateDraftChange(self::ID_SEED)
            ->setReasonForReplacement("NEW_REASON");

        $change->setVin($vin);
        $change->setVrm(strrev($vrm)); // vrm change

        //when
        $updatedDraft = $this->createSUT()->updateDraft($draft, $change);

        //then
        $this->assertTrue($updatedDraft->includeInMismatchFile());
    }

    public function testSetIncludeInPassFile_isAlwaysTrue_forDVSAUser_Only_VRM_Changed()
    {
        //given
        $this->returnsOkCheckResult();

        $this->permissionsGranted(
            [
                PermissionInSystem::CERTIFICATE_REPLACEMENT,
                PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS
            ]
        );

        $vin = '123456';
        $vrm = '123456';

        $draft = RCOF::replacementCertificateDraft()->setReplacementReason("REASON");
        $draft->getMotTest()->setVin($vin);
        $draft->setVin($vin);
        $draft->getMotTest()->setRegistration($vrm);
        $draft->setVrm($vrm);

        $change = RCOF::fullReplacementCertificateDraftChange(self::ID_SEED)
            ->setReasonForReplacement("NEW_REASON");

        $change->setVin($vin);
        $change->setVrm(strrev($vrm)); // vrm change

        //when
        $updatedDraft = $this->createSUT()->updateDraft($draft, $change);

        //then
        $this->assertTrue($updatedDraft->includeInPassFile());
    }

    public function testSetIncludeInMismatchFile_isAlwaysTrue_forDVSAUser_Only_Vin_Changed()
    {
        //given
        $this->returnsOkCheckResult();

        $this->permissionsGranted(
            [
                PermissionInSystem::CERTIFICATE_REPLACEMENT,
                PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS
            ]
        );

        $vin = '123456';
        $vrm = '123456';

        $draft = RCOF::replacementCertificateDraft()->setReplacementReason("REASON");
        $draft->getMotTest()->setVin($vin);
        $draft->setVin($vin);
        $draft->getMotTest()->setRegistration($vrm);
        $draft->setVrm($vrm);

        $change = RCOF::fullReplacementCertificateDraftChange(self::ID_SEED)
            ->setReasonForReplacement("NEW_REASON");

        $change->setVin(strrev($vin)); //vin change
        $change->setVrm($vrm);

        //when
        $updatedDraft = $this->createSUT()->updateDraft($draft, $change);

        //then
        $this->assertTrue($updatedDraft->includeInMismatchFile());
    }

    public function testSetIncludeInPassFile_isAlwaysFalse_forDVSAUser_Only_Vin_Changed()
    {
        //given
        $this->returnsOkCheckResult();

        $this->permissionsGranted(
            [
                PermissionInSystem::CERTIFICATE_REPLACEMENT,
                PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS
            ]
        );

        $vin = '123456';
        $vrm = '123456';

        $draft = RCOF::replacementCertificateDraft()->setReplacementReason("REASON");
        $draft->getMotTest()->setVin($vin);
        $draft->setVin($vin);
        $draft->getMotTest()->setRegistration($vrm);
        $draft->setVrm($vrm);

        $change = RCOF::fullReplacementCertificateDraftChange(self::ID_SEED)
            ->setReasonForReplacement("NEW_REASON");

        $change->setVin(strrev($vin)); //vin change
        $change->setVrm($vrm);

        //when
        $updatedDraft = $this->createSUT()->updateDraft($draft, $change);

        //then
        $this->assertFalse($updatedDraft->includeInPassFile());
    }

    public function testSetIncludeInMismatchFile_isAlwaysFalse_forDVSAUser_Only_Expiry_changed()
    {
        //given
        $this->returnsOkCheckResult();

        $this->permissionsGranted(
            [
                PermissionInSystem::CERTIFICATE_REPLACEMENT,
                PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS
            ]
        );

        $vin = '123456';
        $vrm = '123456';

        $draft = RCOF::replacementCertificateDraft()->setReplacementReason("REASON");
        $draft->getMotTest()->setVin($vin);
        $draft->setVin($vin);
        $draft->getMotTest()->setRegistration($vrm);
        $draft->setVrm($vrm);

        $change = RCOF::fullReplacementCertificateDraftChange(self::ID_SEED)
            ->setReasonForReplacement("NEW_REASON");

        $change->setVin($vin);
        $change->setVrm($vrm);
        $change->setExpiryDate('2015-02-01');

        //when
        $updatedDraft = $this->createSUT()->updateDraft($draft, $change);

        //then
        $this->assertFalse($updatedDraft->includeInMismatchFile());
    }

    public function testSetIncludeInPassFile_isAlwaysTrue_forDVSAUser_Only_Expiry_changed()
    {
        //given
        $this->returnsOkCheckResult();

        $this->permissionsGranted(
            [
                PermissionInSystem::CERTIFICATE_REPLACEMENT,
                PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS
            ]
        );

        $vin = '123456';
        $vrm = '123456';

        $draft = RCOF::replacementCertificateDraft()->setReplacementReason("REASON");
        $draft->getMotTest()->setVin($vin);
        $draft->setVin($vin);
        $draft->getMotTest()->setRegistration($vrm);
        $draft->setVrm($vrm);

        $change = RCOF::fullReplacementCertificateDraftChange(self::ID_SEED)
            ->setReasonForReplacement("NEW_REASON");

        $change->setVin($vin);
        $change->setVrm($vrm);
        $change->setExpiryDate('2015-02-01');

        //when
        $updatedDraft = $this->createSUT()->updateDraft($draft, $change);

        //then
        $this->assertTrue($updatedDraft->includeInPassFile());
    }

    public function testSetIncludeInMismatchFile_isAlwaysFalse_forDVSAUser_Only_Make_Changed()
    {
        //given
        $this->returnsOkCheckResult();

        $this->permissionsGranted(
            [
                PermissionInSystem::CERTIFICATE_REPLACEMENT,
                PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS
            ]
        );

        $vin = '123456';
        $vrm = '123456';

        $draft = RCOF::replacementCertificateDraft()->setReplacementReason("REASON");
        $draft->getMotTest()->setVin($vin);
        $draft->setVin($vin);
        $draft->getMotTest()->setRegistration($vrm);
        $draft->setVrm($vrm);

        $change = RCOF::fullReplacementCertificateDraftChange(self::ID_SEED)
            ->setMake("18800")
            ->setReasonForReplacement("NEW_REASON");

        $change->setVin($vin);
        $change->setVrm($vrm); // vrm change

        //when
        $updatedDraft = $this->createSUT()->updateDraft($draft, $change);

        //then
        $this->assertFalse($updatedDraft->includeInMismatchFile());
    }

    public function testSetIncludeInPassFile_isAlwaysFalse_forDVSAUser_Only_Make_Changed()
    {
        //given
        $this->returnsOkCheckResult();

        $this->permissionsGranted(
            [
                PermissionInSystem::CERTIFICATE_REPLACEMENT,
                PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS
            ]
        );

        $vin = '123456';
        $vrm = '123456';

        $draft = RCOF::replacementCertificateDraft()->setReplacementReason("REASON");
        $draft->getMotTest()->setVin($vin);
        $draft->setVin($vin);
        $draft->getMotTest()->setRegistration($vrm);
        $draft->setVrm($vrm);

        $change = RCOF::fullReplacementCertificateDraftChange(self::ID_SEED)
            ->setMake("18800")
            ->setReasonForReplacement("NEW_REASON");

        $change->setVin($vin);
        $change->setVrm($vrm); // vrm change

        //when
        $updatedDraft = $this->createSUT()->updateDraft($draft, $change);

        //then
        $this->assertFalse($updatedDraft->includeInPassFile());
    }

    public function testSetIsVinVrmExpiryChanged_isAlwaysTrue_forDVLAChangePermission_Only_VIN_Changed()
    {
        //given
        $this->returnsOkCheckResult();

        $this->permissionsGranted(
            [
                PermissionInSystem::CERTIFICATE_REPLACEMENT_DVLA_CHANGE,
                PermissionInSystem::CERTIFICATE_REPLACEMENT,
                PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS
            ]
        );

        $vin = '123456';
        $vrm = '123456';

        $draft = RCOF::replacementCertificateDraft()->setReplacementReason("REASON");
        $draft->getMotTest()->setVin($vin);
        $draft->setVin($vin);
        $draft->getMotTest()->setRegistration($vrm);
        $draft->setVrm($vrm);

        $change = RCOF::partialReplacementCertificateDraftChange(self::ID_SEED)->setReasonForReplacement("NEW_REASON");
        // expiry date must not be set for 'isVinVrmExpiryChanged' to remain false
        $change->setMake(999 + self::ID_SEED)
            ->setModel(999 + self::ID_SEED)
            ->setCountryOfRegistration(999 + self::ID_SEED)
            ->setVtsSiteNumber("NEW_SITE_NUMBER");
        $change->setVin('123457'); // vin change
        $change->setVrm($vrm);

        //when
        $updatedDraft = $this->createSUT()->updateDraft($draft, $change);

        //then
        $this->assertTrue($updatedDraft->getIsVinVrmExpiryChanged());
    }

    public function testSetIsVinVrmExpiryChanged_isAlwaysFalse_forDVLAChangePermission_Only_VRM_Changed()
    {
        //given
        $this->returnsOkCheckResult();

        $this->permissionsGranted(
            [
                PermissionInSystem::CERTIFICATE_REPLACEMENT,
                PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS,
                PermissionInSystem::CERTIFICATE_REPLACEMENT_DVLA_CHANGE,
            ]
        );

        $vin = '123456';
        $vrm = '123456';

        $draft = RCOF::replacementCertificateDraft()->setReplacementReason("REASON");
        $draft->getMotTest()->setVin($vin);
        $draft->setVin($vin);
        $draft->getMotTest()->setRegistration($vrm);
        $draft->setVrm($vrm);

        $change = RCOF::partialReplacementCertificateDraftChange(self::ID_SEED)->setReasonForReplacement("NEW_REASON");
        // expiry date must not be set for 'isVinVrmExpiryChanged' to remain false
        $change->setMake(999 + self::ID_SEED)
            ->setModel(999 + self::ID_SEED)
            ->setCountryOfRegistration(999 + self::ID_SEED)
            ->setVtsSiteNumber("NEW_SITE_NUMBER");
        $change->setVin($vin);
        $change->setVrm('123457');

        //when
        $updatedDraft = $this->createSUT()->updateDraft($draft, $change);

        //then
        $this->assertTrue($updatedDraft->getIsVinVrmExpiryChanged());
    }

    public function testUpdate_givenFullRights_and_reasonForDifferentTester_shouldForbidAction()
    {
        $this->returnsOkCheckResult();

        $this->setExpectedException(ForbiddenException::class);
        $draft = RCOF::replacementCertificateDraft()->setReplacementReason("REASON");
        $change = RCOF::fullReplacementCertificateDraftChange(self::ID_SEED)->setReasonForDifferentTester("NEW_REASON");
        $this->permissionsGranted(
            [PermissionInSystem::CERTIFICATE_REPLACEMENT, PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS]
        );
        $this->createSUT()->updateDraft($draft, $change);
    }

    public function testUpdate_givenPartialRights_asDifferentTester_shouldExpectReasonSet()
    {
        $draft = RCOF::replacementCertificateDraft();
        $change = RCOF::partialReplacementCertificateDraftChange(self::ID_SEED)->setReasonForDifferentTester("HALO");

        $this->returnsOkCheckResult();
        $this->userAssignedToVts(false);
        $this->returnsReasonForDifferentTester(RCOF::reasonForDifferentTester($change->getReasonForDifferentTester()));
        $updatedDraft = $this->createSUT()->updateDraft($draft, $change);
        $this->assertEquals(
            $change->getReasonForDifferentTester(),
            $updatedDraft->getReasonForDifferentTester()->getCode()
        );
    }

    public function testUpdate_givenFullRights_shouldUpdateDraftAccordingly()
    {
        $this->returnsOkCheckResult();

        $draft = RCOF::replacementCertificateDraft()->setReplacementReason("REASON");
        $change = RCOF::fullReplacementCertificateDraftChange(self::ID_SEED)->setReasonForReplacement("NEW_REASON");
        $this->permissionsGranted(
            [PermissionInSystem::CERTIFICATE_REPLACEMENT, PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS]
        );

        $updatedDraft = $this->createSUT()->updateDraft($draft, $change);

        $this->assertEquals($change->getPrimaryColour(), $updatedDraft->getPrimaryColour()->getCode());
        $this->assertEquals($change->getSecondaryColour(), $updatedDraft->getSecondaryColour()->getCode());
        $this->assertEquals($change->getMake(), $updatedDraft->getMake()->getId());
        $this->assertEquals($change->getModel(), $updatedDraft->getModel()->getId());
        $this->assertEquals($change->getCountryOfRegistration(), $updatedDraft->getCountryOfRegistration()->getId());
        $this->assertEquals(
            $change->getExpiryDate(),
            DateTimeApiFormat::date($updatedDraft->getExpiryDate())
        );
        $this->assertEquals($change->getReasonForReplacement(), $updatedDraft->getReplacementReason());
        $this->assertEquals(
            $change->getOdometerReading()->getResultType(),
            $updatedDraft->getOdometerReading()->getResultType()
        );
        $this->assertEquals($change->getVin(), $updatedDraft->getVin());
        $this->assertEquals($change->getVrm(), $updatedDraft->getVrm());
        $this->assertEquals($change->getVtsSiteNumber(), $updatedDraft->getVehicleTestingStation()->getSiteNumber());
    }

    public function testUpdateReplacementCertificateWithUpdatedVrm()
    {
        $this->returnsOkCheckResult();

        $this->permissionsGranted(
            [PermissionInSystem::CERTIFICATE_REPLACEMENT, PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS]
        );

        $draft = RCOF::replacementCertificateDraft()->setReplacementReason("REASON");
        $draft->getMotTest()->setRegistration('123456');
        $draft->setVrm('123456');
        $draft->setVin('123456');

        $change = RCOF::fullReplacementCertificateDraftChange(self::ID_SEED)->setReasonForReplacement("NEW_REASON");
        $change->setVin('123456');
        $change->setVrm('7891011');

        $updatedDraft = $this->createSUT()->updateDraft($draft, $change);

        $this->assertEquals(1, $updatedDraft->getIsVinVrmExpiryChanged());
    }

    public function testUpdateReplacementCertificateWithUpdatedExpiryDate()
    {
        $this->returnsOkCheckResult();

        $this->permissionsGranted(
            [PermissionInSystem::CERTIFICATE_REPLACEMENT, PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS]
        );

        $draft = RCOF::replacementCertificateDraft()->setReplacementReason("REASON");
        $draft->setExpiryDate(DateUtils::toDate("2014-01-01"));

        $change = RCOF::fullReplacementCertificateDraftChange(self::ID_SEED)->setReasonForReplacement("NEW_REASON");
        $change->setExpiryDate("2014-02-02");

        $updatedDraft = $this->createSUT()->updateDraft($draft, $change);

        $this->assertEquals(1, $updatedDraft->getIsVinVrmExpiryChanged());
    }

    public function testUpdateReplacementCertificateWithUpdatedVin()
    {
        $this->returnsOkCheckResult();

        $this->permissionsGranted(
            [PermissionInSystem::CERTIFICATE_REPLACEMENT, PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS]
        );

        $draft = RCOF::replacementCertificateDraft()->setReplacementReason("REASON");
        $draft->getMotTest()->setVin('123456');
        $draft->setVin('123456');
        $draft->setVrm('123456');
        $draft->setExpiryDate(DateUtils::toDate("2014-01-01"));
        $draft->setIsVinVrmExpiryChanged(false);

        $change = RCOF::fullReplacementCertificateDraftChange(self::ID_SEED)->setReasonForReplacement("NEW_REASON");
        $change->setVin('7891011');
        $change->setVrm('123456');
        $change->setExpiryDate("2014-01-01");

        $updatedDraft = $this->createSUT()->updateDraft($draft, $change);

        $this->assertTrue($updatedDraft->getIsVinVrmExpiryChanged());
    }

    public function testUpdateReplacementCertificateWithSameVinAndVrmAndExpiryDateShouldNotUpdateRegistrationChangedStatus()
    {
        $this->returnsOkCheckResult();

        $this->permissionsGranted(
            [PermissionInSystem::CERTIFICATE_REPLACEMENT, PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS]
        );

        $draft = RCOF::replacementCertificateDraft()->setReplacementReason("REASON");
        $draft->getMotTest()->setVin('123456');
        $draft->getMotTest()->setExpiryDate(DateUtils::toDate("2014-01-01"));
        $draft->setExpiryDate(DateUtils::toDate("2014-01-01"));
        $draft->setVin('123456');
        $draft->setVrm('123456');
        $draft->setIsVinVrmExpiryChanged(false);

        $change = RCOF::fullReplacementCertificateDraftChange(self::ID_SEED)->setReasonForReplacement("NEW_REASON");
        $draft->getMotTest()->setRegistration('123456');
        $change->setExpiryDate("2014-01-01");
        $change->setVin('123456');
        $change->setVrm('123456');

        $updatedDraft = $this->createSUT()->updateDraft($draft, $change);

        $this->assertEquals(0, $updatedDraft->getIsVinVrmExpiryChanged());
    }

    public function testUpdateReplacementCertificateWithCustomMakeShouldSetCustomMakeAndMakeIdNullStatus()
    {
        $this->returnsOkCheckResult();

        $this->permissionsGranted(
            [PermissionInSystem::CERTIFICATE_REPLACEMENT, PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS]
        );

        $draft = RCOF::replacementCertificateDraft()->setReplacementReason("REASON");
        $draft->getMotTest()->setVin('123456');
        $draft->setMake(
            (new Make())->setId(1)->setCode('test')->setName('test2')
        );
        $draft->setMakeName(null);

        $change = RCOF::fullReplacementCertificateDraftChange(self::ID_SEED)->setReasonForReplacement("NEW_REASON");
        $change->setCustomMake('TOYOTA UK');

        $updatedDraft = $this->createSUT()->updateDraft($draft, $change);

        $this->assertNull($draft->getMake());
        $this->assertEquals('TOYOTA UK', $updatedDraft->getMakeName());
    }

    /**
     * We are testing if a Custom Model name is set, then the Model ID or the Model Entity associated becomes null.
     * We do not want confusion between a Custom Model name and an associated Model.  Both are seperate.
     */
    public function testUpdateReplacementCertificateWithCustomModelShouldSetCustomModelAndModelIdNullStatus()
    {
        $this->returnsOkCheckResult();

        $this->permissionsGranted(
            [PermissionInSystem::CERTIFICATE_REPLACEMENT, PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS]
        );

        $draft = RCOF::replacementCertificateDraft()->setReplacementReason("REASON");
        $draft->getMotTest()->setVin('123456');
        $draft->setModel((new Model)->setId(1)->setCode('T')->setName('B'));
        $draft->setModelName(null);
        $draft->setMake(false);
        $draft->setMakeName('Test');

        $change = RCOF::fullReplacementCertificateDraftChange(self::ID_SEED)->setReasonForReplacement("NEW_REASON");
        $change->setCustomMake('TEST');
        $change->setCustomModel('SUPRA UK');

        $updatedDraft = $this->createSUT()->updateDraft($draft, $change);

        $this->assertNull($updatedDraft->getModel());
        $this->assertEquals('SUPRA UK', $updatedDraft->getModelName());
    }

    public function testChangeInExpiryDateShouldUpdateIsVinVrmExpiryChanged()
    {
        //given
        $this->returnsOkCheckResult();

        $this->permissionsGranted(
            [PermissionInSystem::CERTIFICATE_REPLACEMENT, PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS]
        );

        $draft = RCOF::replacementCertificateDraft()->setReplacementReason("REASON");
        $draft->getMotTest()->setExpiryDate(DateUtils::toDate("2013-12-01"));
        $draft->getMotTest()->setVin('123456');
        $draft->getMotTest()->setRegistration('123456');

        $change = RCOF::fullReplacementCertificateDraftChange(self::ID_SEED)->setReasonForReplacement("NEW_REASON");
        $change->setExpiryDate("2014-01-01");
        $change->setVin('123456');
        $change->setVrm('123456');

        //when
        $updatedDraft = $this->createSUT()->updateDraft($draft, $change);

        //then
        $this->assertEquals(1, $updatedDraft->getIsVinVrmExpiryChanged());
    }

    private function returnsMakeByCode(Make $make)
    {
        $this->vehDict->expects($this->any())->method('findMakeByCode')
            ->will($this->returnValue($make));
    }

    private function returnsMake(Make $make)
    {
        $this->vehDict->expects($this->any())->method('getMake')
            ->will($this->returnValue($make));
    }

    private function returnsModel(Model $model)
    {
        $this->vehDict->expects($this->any())->method('getModel')
            ->will($this->returnValue($model));
    }

    private function returnsReasonForDifferentTester(
        CertificateChangeDifferentTesterReason $reason
    ) {
        $this->certificateChangeReasonRepository->expects($this->any())->method("getByCode")
            ->will($this->returnValue($reason));
    }

    private function returnsCountryOfRegistration(
        CountryOfRegistration $cor
    ) {
        $this->vehDict->expects($this->any())->method('getCountryOfRegistration')
            ->will($this->returnValue($cor));
    }

    private function returnsVts(
        Site $vts
    ) {
        $this->vtsRepository->expects($this->any())->method('getBySiteNumber')
            ->will($this->returnValue($vts));
    }

    private function returnsColours(
        Colour $primary,
        Colour $secondary
    ) {
        $this->vehDict->expects($this->any())->method('getColourByCode')
            ->will(
                MultiCallStubBuilder::of()
                    ->add([$primary->getCode(), $this->anything()], $primary)
                    ->add([$secondary->getCode(), $this->anything()], $secondary)
                    ->build()
            );
    }

    private function permissionsGranted(
        $permissions
    ) {
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

    public function createSUT()
    {
        $service = new ReplacementCertificateDraftUpdater(
            $this->motTestSecurityService,
            $this->authorizationService,
            $this->vehDict,
            $this->certificateChangeReasonRepository,
            $this->vtsRepository,
            $this->motIdentityProvider,
            $this->replacementCertificateDraftChangeValidator,
            $this->odometerHolderUpdater
        );

        TestTransactionExecutor::inject($service);

        return $service;
    }

    private function userAssignedToVts($decision = true)
    {
        $this->motTestSecurityService->expects($this->any())
            ->method('isCurrentTesterAssignedToMotTest')
            ->will($this->returnValue($decision));
    }

    private function returnsOkCheckResult()
    {
        $this->replacementCertificateDraftChangeValidator->expects($this->any())->method('validate')
            ->will($this->returnValue(CheckResult::ok()));
    }
}
