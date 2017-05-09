<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\EntityNotFoundException;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\CertificateReplacementDraft;
use DvsaEntities\Entity\MotTest;

/**
 * Class ReplacementCertificateDraftRepository.
 *
 * @codeCoverageIgnore
 */
class ReplacementCertificateDraftRepository extends AbstractMutableRepository
{
    /**
     * @param string $draftId
     *
     * @return CertificateReplacementDraft
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function get($draftId)
    {
        $result = $this->find($draftId);
        if (!$result) {
            throw new NotFoundException('Replacement certificate draft', $draftId);
        }

        $this->loadMotTest($result);

        return $result;
    }

    /**
     * The MotTest is lazy loaded, but since it might be either in the mot_test_current or the mot_test_history table,
     * we need to look at both of these places.
     *
     * @param CertificateReplacementDraft $draft
     */
    public function loadMotTest(CertificateReplacementDraft $draft)
    {
        try {
            // getting a property will trigger lazy loading
            $draft->getMotTest()->getNumber();
            if ($draft->getMotTest()->getPrsMotTest()) {
                $draft->getMotTest()->getPrsMotTest()->getNumber();
            }

            return;
        } catch (EntityNotFoundException $e) {
        }

        try {
            $classMetadata = $this->_em->getClassMetadata(MotTest::class);
            $classMetadata->setTableName(
                str_replace(MotTestHistoryRepository::SUFFIX_CURRENT, MotTestHistoryRepository::SUFFIX_HISTORY, $classMetadata->getTableName())
            );
            $draft->getMotTest()->getNumber();
            if ($draft->getMotTest()->getPrsMotTest()) {
                $draft->getMotTest()->getPrsMotTest()->getNumber();
            }
        } finally {
            $classMetadata->setTableName(
                str_replace(MotTestHistoryRepository::SUFFIX_HISTORY, MotTestHistoryRepository::SUFFIX_CURRENT, $classMetadata->getTableName())
            );
        }
    }
}
