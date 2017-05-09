<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace IntegrationApi\DvlaInfo\Mapper;

use DvsaCommon\Enum\MotTestStatusName;
use DvsaEntities\Entity\MotTest;
use IntegrationApi\MotTestCommon\Mapper\AbstractMotTestMapper;

class DvlaInfoMotHistoryMapper extends AbstractMotTestMapper
{
    public function toArray(array $motTests)
    {
        $result = [];

        /** @var $motTest MotTest */
        foreach ($motTests as $motTest) {
            $result[] = [
                'testDate' => $this->returnFormattedDateOrNull($motTest->getCompletedDate()),
                'testResult' => $this->formattedStatus($motTest->getStatus()),
                'odometerValue' => $motTest->getOdometerValue(),
                'odometerUnit' => $motTest->getOdometerUnit(),
                'testNumber' => $motTest->getNumber(),
                'expiryDate' => $this->returnFormattedDateOrNull($motTest->getExpiryDate()),
            ];
        }

        return $result;
    }

    private function formattedStatus($status)
    {
        switch ($status) {
            case MotTestStatusName::PASSED:
                return 'P';
            case MotTestStatusName::FAILED:
                return 'F';
            default:
                return null;
        }
    }
}
