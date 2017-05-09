<?php

namespace OrganisationApi\Service\Mapper;

use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use DvsaCommon\Dto\Organisation\MotTestLogSummaryDto;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Service\Mapper\AbstractApiMapper;

/**
 * Class MotTestLogMapper.
 */
class MotTestLogSummaryMapper extends AbstractApiMapper
{
    /**
     * @param array $logSummary countains count of tests for different intervals
     *
     * @return MotTestLogSummaryDto
     */
    public function toDto($logSummary)
    {
        if (!is_array($logSummary) || empty($logSummary)) {
            throw new InvalidArgumentException('Expect array and not empty parameter');
        }

        $dto = new MotTestLogSummaryDto();
        $dto
            ->setYear(ArrayUtils::tryGet($logSummary, 'year', 0))
            ->setMonth(ArrayUtils::tryGet($logSummary, 'month', 0))
            ->setWeek(ArrayUtils::tryGet($logSummary, 'week', 0))
            ->setToday(ArrayUtils::tryGet($logSummary, 'today', 0));

        return $dto;
    }
}
