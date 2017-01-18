<?php

namespace DvsaMotApiTest\Service\ReplacementCertificate;

use Doctrine\ORM\EntityManager;
use DvsaAuthentication\Service\OtpService;
use DvsaCommon\Enum\CertificateType;
use DvsaCommon\Enum\CertificateTypeCode;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonApiTest\Transaction\TestTransactionExecutor;
use DvsaCommonTest\TestUtils\ArgCapture;
use DvsaCommonTest\TestUtils\XMock;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Auth\MotIdentityInterface;
use DvsaEntities\Entity\CertificateReplacement;
use DvsaEntities\Entity\CertificateReplacementDraft;
use DvsaEntities\Repository\CertificateReplacementRepository;
use DvsaEntities\Repository\CertificateTypeRepository;
use DvsaEntities\Repository\MotTestRepository;
use DvsaEntities\Repository\ReplacementCertificateDraftRepository;
use DvsaMotApi\Service\CertificateCreationService;
use DvsaMotApi\Service\ReplacementCertificate\ReplacementCertificateDraftCreator;
use DvsaMotApi\Service\ReplacementCertificate\ReplacementCertificateDraftUpdater;
use DvsaMotApi\Service\ReplacementCertificate\ReplacementCertificateService;
use DvsaMotApi\Service\ReplacementCertificate\ReplacementCertificateUpdater;
use DvsaMotApiTest\Factory\MotTestObjectsFactory;
use DvsaMotApiTest\Factory\ReplacementCertificateObjectsFactory;
use IntegrationApi\DvlaVehicle\Service\DvlaVehicleUpdatedService;
use PHPUnit_Framework_TestCase;
use DvsaCommon\Auth\PermissionInSystem;

/**
 * Class ReplacementCertificateServiceTest
 */
class ReplacementCertificateServiceTest extends AbstractServiceTestCase
{
    private $entityManager;
    private $authorizationService;
    private $motTestRepository;
    private $draftCreator;
    private $draftUpdater;
    private $certificateCreator;
    private $certificateReplacementRepository;
    private $draftRepository;
    private $otpService;
    private $certificateCreationService;

    /** @var  MotIdentityInterface $motIdentity */
    private $motIdentity;

    /** @var  MotIdentityProviderInterface $motIdentityProvider */
    private $motIdentityProvider;

    public function setUp()
    {
        $transferType = new \DvsaEntities\Entity\CertificateType();
        $transferType->setId(5)
            ->setName('Transfer')
            ->setCode(CertificateTypeCode::TRANSFER);

        $replaceType = new \DvsaEntities\Entity\CertificateType();
        $replaceType->setId(2)
            ->setName('Replace')
            ->setCode(CertificateTypeCode::REPLACE);

        $mockCertificateTypeRepository = $this->getMockBuilder(CertificateTypeRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockCertificateTypeRepository->expects($this->any())
            ->method('getByCode')
            ->will(
                $this->returnValueMap(
                    [
                        [CertificateTypeCode::TRANSFER, $transferType],
                        [CertificateTypeCode::REPLACE, $replaceType]
                    ]
                )
            );

        $this->entityManager = $this->getMockEntityManager();
        $this->entityManager->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($mockCertificateTypeRepository));

        $this->motTestRepository = XMock::of(MotTestRepository::class);
        $this->draftCreator = XMock::of(ReplacementCertificateDraftCreator::class);
        $this->draftUpdater = XMock::of(ReplacementCertificateDraftUpdater::class);
        $this->certificateCreator = XMock::of(ReplacementCertificateUpdater::class);
        $this->certificateReplacementRepository = XMock::of(CertificateReplacementRepository::class);
        $this->draftRepository = XMock::of(ReplacementCertificateDraftRepository::class);
        $this->authorizationService = XMock::of('DvsaAuthorisation\Service\AuthorisationServiceInterface', ['isGranted']);
        $this->otpService = XMock::of(OtpService::class);
        $this->certificateCreationService = XMock::of(CertificateCreationService::class);

        $this->motIdentity = XMock::of(\DvsaAuthentication\Identity::class);
        $this->motIdentityProvider = XMock::of(MotIdentityProviderInterface::class);

        $this->motIdentity->expects($this->any())
            ->method('getUserId')
            ->willReturn(1);

        $this->motIdentityProvider
            ->expects($this->any())
            ->method('getIdentity')
            ->willReturn($this->motIdentity);
    }

    private function createSUT()
    {
        $sut = new ReplacementCertificateService(
            $this->entityManager,
            $this->motIdentityProvider,
            $this->draftRepository,
            $this->draftCreator,
            $this->draftUpdater,
            $this->certificateCreator,
            $this->certificateReplacementRepository,
            $this->authorizationService,
            $this->motTestRepository,
            $this->otpService,
            $this->certificateCreationService
        );

        TestTransactionExecutor::inject($sut);
        return $sut;
    }

    public function testCreateDraft_givenMotTestId_returnCreatedDraftInstance()
    {
        $motTest = MotTestObjectsFactory::motTest()->setNumber(123456789012);
        $replacementDraft = ReplacementCertificateObjectsFactory::replacementCertificateDraft();

        $this->motTestRepository->expects($this->any())
            ->method("getMotTestByNumber")
            ->with($motTest->getNumber())
            ->will($this->returnValue($motTest));
        $this->draftCreator->expects($this->any())
            ->method("create")
            ->with($motTest)
            ->will($this->returnValue($replacementDraft));
        $this->draftRepository->expects($this->any())->method("save");

        $draft = $this->createSUT()->createDraft($motTest->getNumber());

        $this->assertInstanceOf(
            CertificateReplacementDraft::class, $draft,
            "expected draft not returned!"
        );
    }

    public function testCreateAndUpdateDraft_givenMotTestId_callUpdaterAndReturnCreatedDraftInstance()
    {
        $motTest = MotTestObjectsFactory::motTest()->setNumber(123456789012);
        $replacementDraft = ReplacementCertificateObjectsFactory::replacementCertificateDraft();

        $this->motTestRepository->expects($this->any())
            ->method("getMotTestByNumber")
            ->with($motTest->getNumber())
            ->will($this->returnValue($motTest));
        $this->draftCreator->expects($this->any())
            ->method("create")
            ->with($motTest)
            ->will($this->returnValue($replacementDraft));
        $this->draftRepository->expects($this->any())->method("save");

        $changeData = ReplacementCertificateObjectsFactory::partialReplacementCertificateDraftChange(1);
        $this->draftUpdater->expects($this->once())
            ->method("updateDraft")
            ->with($replacementDraft, $changeData);

        $draft = $this->createSUT()->createAndUpdateDraft($motTest->getNumber(), '', $changeData);

        $this->assertInstanceOf(
            CertificateReplacementDraft::class, $draft,
            "expected draft not returned!"
        );
    }

    public function testGetDraft_givenDraftId_returnDraftInstance()
    {
        $draft = ReplacementCertificateObjectsFactory::replacementCertificateDraft()->setId(12345);

        $this->returnsDraftForId($draft->getId(), $draft);

        $draft = $this->createSUT()->getDraft($draft->getId());

        $this->assertNotNull($draft, "retrieved draft instance should not be null");
    }

    public function testUpdateDraft_givenDraftIdAndChangeData_callUpdater()
    {
        $draft = ReplacementCertificateObjectsFactory::replacementCertificateDraft()->setId(12345);
        $changeData = ReplacementCertificateObjectsFactory::partialReplacementCertificateDraftChange(1);
        $this->returnsDraftForId($draft->getId(), $draft);

        $this->draftUpdater->expects($this->once())
            ->method("updateDraft")
            ->with($draft, $changeData);

        $this->createSUT()->updateDraft($draft->getId(), $changeData);
    }

    public function testApplyDraft_givenDraftId_createCorrectCertificateReplacement()
    {
        $exampleReason = "EXAMPLE_REASON";
        $draft = ReplacementCertificateObjectsFactory::replacementCertificateDraft()
            ->setId(12345)->setReasonForReplacement($exampleReason);
        $this->returnsDraftForId($draft->getId(), $draft);
        $certificateReplacementCapture = ArgCapture::create();

        $this->certificateCreator->expects($this->any())->method("create");
        $this->certificateReplacementRepository->expects($this->any())->method("save")
            ->with($certificateReplacementCapture());

        $data = ['oneTimePassword' => '123456'];

        $this->createSUT()->applyDraft($draft->getId(), $data);

        /** @var CertificateReplacement $certReplacement */
        $certReplacement = $certificateReplacementCapture->get();
        $this->assertEquals(
            $exampleReason, $certReplacement->getReplacementReason(),
            "replacement reason of the draft should be copied to certificate replacement instance!"
        );
        $this->assertEquals(
            CertificateTypeCode::REPLACE, $certReplacement->getCertificateType()->getCode(),
            "Certificate replacement type should be 'Replace' for a non-cherished transfer replacement"
        );
        $this->assertNotNull(
            $certReplacement->getMotTest(), "MOT test instance attached to certificate replacement cannot be null!"
        );
        $this->assertEquals(
            $draft->getMotTestVersion(), $certReplacement->getMotTestVersion(),
            'Version of MOT test attached to certificate replacement should point to the changed MOT test version'
        );
    }

    public function testApplyDraft_givenDvlaCherishedTransfer_createCertificateWithCorrectType()
    {
        $reason = DvlaVehicleUpdatedService::CHERISHED_TRANSFER_REASON;
        $draft = ReplacementCertificateObjectsFactory::replacementCertificateDraft()
            ->setId(12346)->setReasonForReplacement($reason);
        $this->returnsDraftForId($draft->getId(), $draft);
        $certificateReplacementCapture = ArgCapture::create();

        $this->certificateCreator->expects($this->any())->method("create");
        $this->certificateReplacementRepository->expects($this->any())->method("save")
            ->with($certificateReplacementCapture());

        $data = ['oneTimePassword' => '123456'];

        $this->createSUT()->applyDraft($draft->getId(), $data);

        $certReplacement = $certificateReplacementCapture->get();
        $this->assertEquals(
            CertificateTypeCode::TRANSFER, $certReplacement->getCertificateType()->getCode(),
            "Certificate replacement type should be 'Transfer' when generated for a cherished transfer"
        );
    }

    /**
     * @return array
     */
    public function dataProviderTestUpdateStatusAuthorisesPinIfNecessary()
    {
        return [
            //Auth Not Expected If Tester Has Two Factor Auth
            [false, true, false],

            //Auth Expected If Tester Does Not Have Two Factor Auth
            [false, false, true],

            //Auth Not Expected If No Otp Permission And Tester Has Two Factor Auth
            [true, true, false],

            //Auth Not Expected If No Otp Permission And Tester Does Not Have Two Factor Auth
            [true, false, false]
        ];
    }

    /**
     * @param bool $isMotTestWithoutOtpPermissionGranted
     * @param bool $isSecondFactorRequiredForIdentity
     * @param bool $isAuthorisationExpected
     *
     * @dataProvider dataProviderTestUpdateStatusAuthorisesPinIfNecessary
     */
    public function testCreateCertificateReplacement_authorisesPinIfNecessary(
        $isMotTestWithoutOtpPermissionGranted,
        $isSecondFactorRequiredForIdentity,
        $isAuthorisationExpected
    ) {
        $exampleReason = "EXAMPLE_REASON";
        $draft = ReplacementCertificateObjectsFactory::replacementCertificateDraft()
            ->setId(12345)->setReasonForReplacement($exampleReason);
        $this->returnsDraftForId($draft->getId(), $draft);
        $certificateReplacementCapture = ArgCapture::create();

        $this->certificateCreator->expects($this->any())->method("create");
        $this->certificateReplacementRepository->expects($this->any())->method("save")
            ->with($certificateReplacementCapture());

        $otpPin = '123456';
        $draftData = ['oneTimePassword' => $otpPin];
        $this->authorizationService
            ->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo(PermissionInSystem::MOT_TEST_WITHOUT_OTP))
            ->willReturn($isMotTestWithoutOtpPermissionGranted);
        $this->motIdentity
            ->expects($this->once())
            ->method('isSecondFactorRequired')
            ->willReturn($isSecondFactorRequiredForIdentity);
        $this->otpService
            ->expects($isAuthorisationExpected ? $this->once() : $this->never())
            ->method('authenticate')
            ->with($otpPin);
        $this->createSUT()->applyDraft($draft->getId(), $draftData);
    }

    private function returnsDraftForId($inputDraftId, $returnedDraft)
    {
        $this->draftRepository->expects($this->any())
            ->method("get")
            ->with($inputDraftId)
            ->will($this->returnValue($returnedDraft));
    }
}
