<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace IntegrationApi\TransportForLondon\Mapper;

use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaEntities\Entity\MotTest;
use IntegrationApi\MotTestCommon\Mapper\AbstractMotTestMapper;

/**
 * Class TransportForLondonMotTestMapper
 */
class TransportForLondonMotTestMapper extends AbstractMotTestMapper
{
    const FLAG_YES = 'Y';
    const FLAG_NO = 'N';
    const FLAG_NA = '';

    public function toArray(MotTest $motTest, $isLaterTestInScope, $isLaterTestOutScope)
    {
        $colour = $motTest->getPrimaryColour();
        $colour2 = $motTest->getSecondaryColour();

        return [
            'colourDesc'        => null === $colour ? null : $colour->getName(),
            'colourDesc2'       => null === $colour2 ? null : $colour2->getName(),
            'makeText'          => $motTest->getMakeName(),
            'modelText'         => $motTest->getModelName(),
            'vehTestDate'       => $this->returnFormattedDateOrNull($motTest->getCompletedDate()),
            'tstExpiryDt'       => $this->returnFormattedDateOrNull($motTest->getExpiryDate()),
            'siteNo'            => $motTest->getVehicleTestingStation()->getSiteNumber(),
            'locTelNo'          => $this->extractPhoneNumber($motTest->getVehicleTestingStation()),
            'recMiles'          => $motTest->getOdometerValue(),
            'recMilesType'      => $motTest->getOdometerUnit(),
            'expiredWarning'    =>
                ($motTest->isPassed() && $this->isInPast($motTest->getExpiryDate())) ? self::FLAG_YES : self::FLAG_NO,
            'laterTestInScope'  => $isLaterTestInScope,
            'laterTestOutScope' => $isLaterTestOutScope,
            'testResult'        => $this->toShortStatus($motTest->getStatus()),
            'vrm'               => $motTest->getRegistration(),
        ];
    }

    private function isInPast(\DateTime $date)
    {
        return $date->modify("midnight") < (new DateTimeHolder())->getCurrentDate();
    }

    private function toShortStatus($status)
    {
        switch ($status) {
            case MotTestStatusName::PASSED:
                return "P";
            case MotTestStatusName::FAILED:
                return "F";
            case MotTestStatusName::REFUSED:
                return "R";
            case MotTestStatusName::ABANDONED:
                return "ABA";
            case MotTestStatusName::ABORTED:
                return "ABR";
            default:
                return null;
        }
    }
}
