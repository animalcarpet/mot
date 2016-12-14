<?php

namespace DvsaMotApi\Service\ReplacementCertificate;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Entity;
use DvsaAuthentication\Service\OtpService;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Enum\CertificateTypeCode;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Transaction\TransactionAwareInterface;
use DvsaCommonApi\Transaction\TransactionAwareTrait;
use DvsaEntities\Entity\CertificateReplacement;
use DvsaEntities\Entity\CertificateType;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Repository\CertificateReplacementRepository;
use DvsaEntities\Repository\CertificateTypeRepository;
use DvsaEntities\Repository\MotTestRepository;
use DvsaEntities\Repository\ReplacementCertificateDraftRepository;
use DvsaMotApi\Dto\ReplacementCertificateDraftChangeDTO;
use DvsaMotApi\Service\CertificateCreationService;

/**
 * Class ReplacementCertificateService
 *
 * @package DvsaMotApi\Service\ReplacementCertificate
 */
class ReplacementCertificateService implements TransactionAwareInterface
{
    use TransactionAwareTrait;

    const CHERISHED_TRANSFER_REASON = 'DVLA Cherished Transfer';

    protected $certificateCreationService;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * @var \DvsaEntities\Repository\ReplacementCertificateDraftRepository $repository
     */
    private $repository;
    /**
     * @var \DvsaAuthorisation\Service\AuthorisationServiceInterface $authService
     */
    private $authService;
    /**
     * @var ReplacementCertificateDraftUpdater $draftUpdater
     */
    private $draftUpdater;
    /**
     * @var ReplacementCertificateDraftCreator $draftCreator
     */
    private $draftCreator;
    /**
     * @var ReplacementCertificateUpdater $certificateUpdater
     */
    private $certificateUpdater;

    /**
     * @var CertificateReplacementRepository $certificateReplacementRepository
     */
    private $certificateReplacementRepository;

    /**
     * @var MotTestRepository $motTestRepository
     */
    private $motTestRepository;

    /**
     * @var OtpService $otpService
     */
    private $otpService;

    /**
     * @var MotIdentityProviderInterface $motIdentityProvider
     */
    private $motIdentityProvider;

    /**
     * @param \Doctrine\ORM\EntityManager           $entityManager
     * @param ReplacementCertificateDraftRepository $repository
     * @param ReplacementCertificateDraftCreator    $draftCreator
     * @param ReplacementCertificateDraftUpdater    $draftUpdater
     * @param ReplacementCertificateUpdater         $certificateUpdater
     * @param CertificateReplacementRepository      $certificateReplacementRepository
     * @param AuthorisationServiceInterface         $authService
     * @param MotTestRepository                     $motTestRepository
     * @param OtpService                            $otpService
     * @param CertificateCreationService            $certificateCreationService
     * @param \DvsaCommon\Auth\MotIdentityProviderInterface $motIdentityProvider
     */
    public function __construct(
        EntityManager $entityManager,
        MotIdentityProviderInterface $motIdentityProvider,
        ReplacementCertificateDraftRepository $repository,
        ReplacementCertificateDraftCreator $draftCreator,
        ReplacementCertificateDraftUpdater $draftUpdater,
        ReplacementCertificateUpdater $certificateUpdater,
        CertificateReplacementRepository $certificateReplacementRepository,
        AuthorisationServiceInterface $authService,
        MotTestRepository $motTestRepository,
        OtpService $otpService,
        CertificateCreationService $certificateCreationService
    ) {
        $this->entityManager = $entityManager;
        $this->motIdentityProvider = $motIdentityProvider;
        $this->repository = $repository;
        $this->authService = $authService;
        $this->draftCreator = $draftCreator;
        $this->draftUpdater = $draftUpdater;
        $this->motTestRepository = $motTestRepository;
        $this->certificateUpdater = $certificateUpdater;
        $this->certificateReplacementRepository = $certificateReplacementRepository;
        $this->otpService = $otpService;
        $this->certificateCreationService = $certificateCreationService;
    }

    /**
     * @param $motTestNumber
     *
     * @return \DvsaEntities\Entity\ReplacementCertificateDraft
     */
    public function createDraft($motTestNumber, $replacementReason = '')
    {
        $this->authService->assertGranted(PermissionInSystem::CERTIFICATE_REPLACEMENT);
        $motTest = $this->motTestRepository->getMotTestByNumber($motTestNumber);
        $draft = $this->draftCreator->create($motTest, $replacementReason);
        $this->repository->save($draft);
        return $draft;
    }

    /**
     * @param                                      $draftId
     * @param ReplacementCertificateDraftChangeDTO $changeDTO
     */
    public function updateDraft($draftId, ReplacementCertificateDraftChangeDTO $changeDTO)
    {
        $this->authService->assertGranted(PermissionInSystem::CERTIFICATE_REPLACEMENT);
        $draft = $this->repository->get($draftId);
        $this->draftUpdater->updateDraft($draft, $changeDTO);
    }

    /**
     * @param string $motTestNumber
     * @param string $replacementReason
     * @param ReplacementCertificateDraftChangeDTO $changeDto
     *
     * @return \DvsaEntities\Entity\ReplacementCertificateDraft
     * @throws \DvsaCommonApi\Service\Exception\ForbiddenException
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function createAndUpdateDraft(
        $motTestNumber,
        $replacementReason,
        ReplacementCertificateDraftChangeDTO $changeDto
    ) {
        $this->authService->assertGranted(PermissionInSystem::CERTIFICATE_REPLACEMENT);
        $motTest = $this->motTestRepository->getMotTestByNumber($motTestNumber);
        $draft = $this->draftCreator->create($motTest, $replacementReason);
        $this->draftUpdater->updateDraft($draft, $changeDto);
        $this->repository->save($draft);
        return $draft;
    }

    /**
     * @param $draftId
     *
     * @return \DvsaEntities\Entity\ReplacementCertificateDraft
     */
    public function getDraft($draftId)
    {
        $this->authService->assertGranted(PermissionInSystem::CERTIFICATE_REPLACEMENT);

        return $this->repository->get($draftId);
    }

    /**
     * Apply the ReplacementCertificateDraft associated with $draftId to a new CertificateReplacement
     * and return a new MOT test with the draft's values.
     *
     * @param integer $draftId
     * @param array $data
     *
     * @param bool $isDvlaImportProcess
     * @return MotTest
     * @throws \DvsaAuthentication\Service\Exception\OtpException
     */
    public function applyDraft($draftId, $data, $isDvlaImportProcess = false)
    {
        $this->authService->assertGranted(PermissionInSystem::CERTIFICATE_REPLACEMENT);

        $userIsAllowedToTestWithoutPin = $this->authService->isGranted(PermissionInSystem::MOT_TEST_WITHOUT_OTP);
        $userHasActivatedA2FaCard = $this->motIdentityProvider->getIdentity()->isSecondFactorRequired();
        if (!$userIsAllowedToTestWithoutPin && !$userHasActivatedA2FaCard) {
            $token = ArrayUtils::tryGet($data, 'oneTimePassword');
            $this->otpService->authenticate($token);
        }

        $motTest = $this->inTransaction(
            function () use ($draftId, $isDvlaImportProcess) {
                $draft = $this->repository->get($draftId);
                $motTest = $this->certificateUpdater->update($draft, $isDvlaImportProcess);

                $certificateReplacement = (new CertificateReplacement())
                    ->setMotTest($draft->getMotTest())
                    ->setMotTestVersion($draft->getMotTestVersion())
                    ->setReasonForDifferentTester($draft->getReasonForDifferentTester())
                    ->setReplacementReason($draft->getReplacementReason())
                    ->setIsVinVrmExpiryChanged($draft->getIsVinVrmExpiryChanged())
                    ->includeInMismatchFile($draft->includeInMismatchFile())
                    ->includeInPassFile($draft->includeInPassFile());

                /** @var CertificateTypeRepository $certTypeRepo */
                $certTypeRepo = $this->entityManager->getRepository(CertificateType::class);

                if ($draft->getReplacementReason() == self::CHERISHED_TRANSFER_REASON) {
                    $certificateReplacement->setCertificateType(
                        $certTypeRepo->getByCode(CertificateTypeCode::TRANSFER)
                    );
                } else {
                    $certificateReplacement->setCertificateType(
                        $certTypeRepo->getByCode(CertificateTypeCode::REPLACE)
                    );
                }

                $this->certificateReplacementRepository->save($certificateReplacement);
                $this->repository->remove($draft);
                return $motTest;
            }
        );

        return $motTest;
    }

    /**
     * @param string $motTestNumber
     * @param int    $userId
     *
     * @return \DvsaCommon\Dto\Common\MotTestDto
     */
    public function createCertificate($motTestNumber, $userId)
    {
        return $this->certificateCreationService->createFromMotTestNumber($motTestNumber, $userId);
    }
}
