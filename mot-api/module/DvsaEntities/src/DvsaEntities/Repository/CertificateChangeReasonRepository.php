<?php

namespace DvsaEntities\Repository;

use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\CertificateChangeDifferentTesterReason;

/**
 * Class CertChangeDiffTesterReasonRepository.
 *
 * @codeCoverageIgnore
 */
class CertificateChangeReasonRepository extends AbstractMutableRepository
{
    /**
     * @param $id
     *
     * @return CertificateChangeDifferentTesterReason
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function getById($id)
    {
        $reason = $this->find($id);
        if ($reason === null) {
            throw new NotFoundException('Reason for different tester changing certificate was not found');
        }

        return $reason;
    }

    /**
     * @param $code
     *
     * @return CertificateChangeDifferentTesterReason
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function getByCode($code)
    {
        $reason = $this->findOneBy(['code' => $code]);
        if (is_null($reason)) {
            throw new NotFoundException("Reason of code $code for different tester changing certificate was not found");
        }

        return $reason;
    }
}
