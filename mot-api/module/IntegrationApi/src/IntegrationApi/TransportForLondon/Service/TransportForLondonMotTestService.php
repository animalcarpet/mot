<?php

namespace IntegrationApi\TransportForLondon\Service;

use DvsaCommon\Date\DateUtils;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Repository\MotTestRepository;
use IntegrationApi\TransportForLondon\Mapper\TransportForLondonMotTestMapper as Mapper;

/**
 * Class TransportForLondonMotTestService.
 */
class TransportForLondonMotTestService
{
    private $repository;
    private $mapper;

    public function __construct(MotTestRepository $repository)
    {
        $this->repository = $repository;
        $this->mapper = new Mapper();
    }

    /**
     * Returns MOT Test data. Searches for (importance order):
     * 1. Unexpired Pass
     * 2. Abandon or Fail
     * 3. Expired Pass
     * Sets expiredWarning, laterTestInScope and laterTestOutScope information flags.
     *
     * @param $vrm
     * @param $v5c
     *
     * @return array
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function getMotTest($vrm, $v5c)
    {
        $motTest = $this->getLastRelevantMotTest($vrm, $v5c);
        $issuedDate = $motTest->getIssuedDate();

        $isLaterTestOutScopeFlag = $this->repository->isAnyWithDifferentV5cReferenceIssuedAfter($vrm, $v5c, $issuedDate)
            ? Mapper::FLAG_YES : Mapper::FLAG_NO;

        if (MotTestStatusName::PASSED === $motTest->getStatus()) {
            $isLaterTestInScopeFlag = $this->isAnyNonPassIssuedAfter($vrm, $v5c, $issuedDate)
                ? Mapper::FLAG_YES : Mapper::FLAG_NO;

            return $this->mapper->toArray($motTest, $isLaterTestInScopeFlag, $isLaterTestOutScopeFlag);
        }

        return $this->mapper->toArray($motTest, Mapper::FLAG_NA, $isLaterTestOutScopeFlag);
    }

    /**
     * @param $vrm
     * @param $v5c
     *
     * @return MotTest
     *
     * @throws NotFoundException
     * @throws \DvsaCommonApi\Service\Exception\ServiceException
     */
    private function getLastRelevantMotTest($vrm, $v5c)
    {
        /** @var $lastPass MotTest */
        $lastPass = $this->repository->findLastPass($vrm, $v5c);

        if ($lastPass) {
            $lastNonPassAfterPass = $this->repository->findNonPassIssuedAfter($vrm, $v5c, $lastPass->getIssuedDate());

            if ($this->isUnexpired($lastPass) || !$lastNonPassAfterPass) {
                return $lastPass;
            } else {
                return $lastNonPassAfterPass;
            }
        } else {
            $lastNonPass = $this->repository->findNonPassIssuedAfter($vrm, $v5c, null);

            if ($lastNonPass) {
                return $lastNonPass;
            } else {
                throw new NotFoundException('MOT test');
            }
        }
    }

    private function isUnexpired(MotTest $motTest)
    {
        return DateUtils::compareDates($motTest->getExpiryDate(), DateUtils::today()) > 0;
    }

    private function isAnyNonPassIssuedAfter($vrm, $v5c, $issuedDate)
    {
        return null !== $this->repository->findNonPassIssuedAfter($vrm, $v5c, $issuedDate);
    }
}
