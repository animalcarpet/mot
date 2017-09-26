<?php

namespace DvsaMotApi\Service\ReplacementCertificate;

use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Enum\CertificateTypeCode;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaDocument\Entity\Document;
use DvsaEntities\Entity\CertificateReplacement;
use DvsaEntities\Entity\CertificateReplacementDraft;
use DvsaEntities\Entity\CertificateType;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Repository\CertificateReplacementRepository;
use DvsaEntities\Repository\CertificateTypeRepository;
use DvsaEntities\Repository\MotTestHistoryRepository;
use DvsaMotApi\Helper\MysteryShopperHelper;
use DvsaMotApi\Service\CertificateCreationService;
use DvsaMotApi\Service\Mapper\MotTestMapper;

class CertificateOdometerHistoryUpdater
{
    /**
     * @var MotTestHistoryRepository
     */
    private $motTestRepository;

    /**
     * @var CertificateTypeRepository
     */
    private $certificateTypeRepository;

    /**
     * @var CertificateCreationService
     */
    private $certificateCreationService;

    /**
     * @var MotTestMapper
     */
    private $motTestMapper;

    /**
     * @var MysteryShopperHelper
     */
    private $mysteryShopperHelper;

    /**
     * @var CertificateReplacementRepository
     */
    private $certificateReplacementRepository;

    /**
     * @var MotIdentityProviderInterface
     */
    private $identityProvider;

    /**
     * @param MotTestHistoryRepository $motTestRepository
     * @param CertificateTypeRepository $certificateTypeRepository
     * @param CertificateCreationService $certificateCreationService
     * @param MotTestMapper $motTestMapper
     * @param MysteryShopperHelper $mysteryShopperHelper
     * @param CertificateReplacementRepository $certificateReplacementRepository
     * @param MotIdentityProviderInterface $identityProvider
     */
    public function __construct(
        MotTestHistoryRepository $motTestRepository,
        CertificateTypeRepository $certificateTypeRepository,
        CertificateCreationService $certificateCreationService,
        MotTestMapper $motTestMapper,
        MysteryShopperHelper $mysteryShopperHelper,
        CertificateReplacementRepository $certificateReplacementRepository,
        MotIdentityProviderInterface $identityProvider
    )
    {
        $this->motTestRepository = $motTestRepository;
        $this->certificateTypeRepository = $certificateTypeRepository;
        $this->certificateCreationService = $certificateCreationService;
        $this->motTestMapper = $motTestMapper;
        $this->mysteryShopperHelper = $mysteryShopperHelper;
        $this->certificateReplacementRepository = $certificateReplacementRepository;
        $this->identityProvider = $identityProvider;
    }


    /**
     * @param MotTest $originalMotTest
     * @param CertificateReplacementDraft $originalDraft
     */
    public function updateOdometerHistoryOnSubsequentCertificates(MotTest $originalMotTest, CertificateReplacementDraft $originalDraft)
    {
        /** @var array|MotTest[] $testsToUpdate */
        $testsToUpdate = $this->fetchSubsequentMotTestsEligibleForUpdate($originalMotTest);
        /** @var MotTest $testToBeUpdated */
        foreach ($testsToUpdate as $toBeUpdatedTest) {

            $this->generateNewCertificate($toBeUpdatedTest);
            $this->motTestRepository->persist($toBeUpdatedTest);

            // we treat every change to MOT Test here as a standard certificate replacement
            $certificateReplacement = $this->generateNewReplacementCertificateEntity($toBeUpdatedTest, $originalDraft);
            $this->certificateReplacementRepository->persist($certificateReplacement);
        }
    }

    /**
     * @param MotTest $originalMotTest
     * @param int $maxOdometerHistoryEntries
     *
     * @return array|MotTest[]
     */
    private function fetchSubsequentMotTestsEligibleForUpdate(MotTest $originalMotTest, $maxOdometerHistoryEntries = 3)
    {
        $motTestHistory = $this->motTestRepository->findTestsForVehicle(
            $originalMotTest->getVehicle()->getId(),
            $originalMotTest->getIssuedDate(),
            $this->mysteryShopperHelper
        );

        // filter all passing mot tests and exclude $currentMotTest from that list
        $motTestHistory = array_filter(
            $motTestHistory,
            function(MotTest $test) use ($originalMotTest) {
                return
                    $test->getStatus() === MotTestStatusName::PASSED &&
                    $test->getNumber() !== $originalMotTest->getNumber();
            }
        );

        $motTestHistory = array_reverse($motTestHistory);

        // we will need to update up to $maxOdometerHistoryEntries of certificates that are newer that the $currentMotTest
        $motTestHistory =  array_slice($motTestHistory, 0, $maxOdometerHistoryEntries);

        return $motTestHistory;
    }

    /**
     * @param MotTest $motTest
     */
    private function generateNewCertificate(MotTest $motTest)
    {
        $motTestDto = $this->motTestMapper->mapMotTest($motTest);
        $userId = $this->identityProvider->getIdentity()->getUserId();

        $this->certificateCreationService->create(
            $motTest->getNumber(),
            $motTestDto,
            $userId
        );
    }

    /**
     * @param MotTest $toBeUpdatedTest
     * @param CertificateReplacementDraft $originalDraft
     *
     * @return CertificateReplacement
     */
    private function generateNewReplacementCertificateEntity(MotTest $toBeUpdatedTest, CertificateReplacementDraft $originalDraft)
    {
        $certificateReplacement = (new CertificateReplacement())
            ->setMotTest($toBeUpdatedTest)
            ->setMotTestVersion($toBeUpdatedTest->getVersion())
            ->setReasonForDifferentTester($originalDraft->getDifferentTesterReason())
            ->setReplacementReason($originalDraft->getReasonForReplacement())
            ->setIsVinVrmExpiryChanged($originalDraft->isVinVrmExpiryChanged())
            ->includeInMismatchFile($originalDraft->isIncludeInMismatchFile())
            ->includeInPassFile($originalDraft->isIncludeInPassFile());

        if ($originalDraft->getReasonForReplacement() == ReplacementCertificateService::CHERISHED_TRANSFER_REASON) {
            $certificateReplacement->setCertificateType(
                $this->getCertificateType(CertificateTypeCode::TRANSFER)

            );
        } else {
            $certificateReplacement->setCertificateType(
                $this->getCertificateType(CertificateTypeCode::REPLACE)
            );
        }

        return $certificateReplacement;
    }

    /**
     * @param $typeCode
     *
     * @return CertificateType
     */
    private function getCertificateType($typeCode)
    {
        return $this->certificateTypeRepository->getByCode($typeCode);
    }


    /**
     * @param MotTest $motTest
     * @param CertificateReplacementDraft $draft
     *
     * @return bool
     */
    public function isOdometerModified(MotTest $motTest, CertificateReplacementDraft $draft)
    {
        return
            $this->isOdometerValueChanged($motTest, $draft) ||
            $this->isOdometerUnitChanged($motTest, $draft) ||
            $this->isOdometerResultTypeChanged($motTest, $draft);
    }

    /**
     * @param MotTest $motTest
     * @param CertificateReplacementDraft $draft
     *
     * @return bool
     */
    private function isOdometerValueChanged(MotTest $motTest, CertificateReplacementDraft $draft)
    {
        return $motTest->getOdometerValue() != $draft->getOdometerValue();
    }

    /**
     * @param MotTest $motTest
     * @param CertificateReplacementDraft $draft
     *
     * @return bool
     */
    private function isOdometerUnitChanged(MotTest $motTest, CertificateReplacementDraft $draft)
    {
        return $motTest->getOdometerUnit() != $draft->getOdometerUnit();
    }

    /**
     * @param MotTest $motTest
     * @param CertificateReplacementDraft $draft
     *
     * @return bool
     */
    private function isOdometerResultTypeChanged(MotTest $motTest, CertificateReplacementDraft $draft)
    {
        return $motTest->getOdometerResultType() != $draft->getOdometerResultType();
    }
}