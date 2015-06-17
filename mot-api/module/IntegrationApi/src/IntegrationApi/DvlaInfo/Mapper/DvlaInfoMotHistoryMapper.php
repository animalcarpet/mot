<?php

namespace IntegrationApi\DvlaInfo\Mapper;

use Doctrine\Common\Collections\Collection;
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
                'testDate'        => $this->returnFormattedDateOrNull($motTest->getCompletedDate()),
                'testResult'      => $this->formattedStatus($motTest->getStatus()),
                'odometerValue'   => $motTest->getOdometerReading()->getValue(),
                'odometerUnit'    => $motTest->getOdometerReading()->getUnit(),
                'testNumber'      => $motTest->getNumber(),
                'expiryDate'      => $this->returnFormattedDateOrNull($motTest->getExpiryDate())
            ];
        }

        return $result;
    }

    private function formattedStatus($status)
    {
        switch ($status) {
            case MotTestStatusName::PASSED:
                return "P";
            case MotTestStatusName::FAILED:
                return "F";
            default:
                return null;
        }
    }
}
