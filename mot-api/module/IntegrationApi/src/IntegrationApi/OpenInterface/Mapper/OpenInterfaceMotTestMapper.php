<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace IntegrationApi\OpenInterface\Mapper;

use DvsaEntities\Entity\DvlaVehicle;
use DvsaEntities\Entity\MotTest;
use IntegrationApi\MotTestCommon\Mapper\AbstractMotTestMapper;

/**
 * Class OpenInterfaceMotTestMapper.
 */
class OpenInterfaceMotTestMapper extends AbstractMotTestMapper
{
    public function toArray(MotTest $motTest)
    {
        $colour = $motTest->getPrimaryColour();
        $colour2 = $motTest->getSecondaryColour();

        return [
            'vrm' => $motTest->getRegistration(),
            'make' => $motTest->getMakeName(),
            'model' => $motTest->getModelName(),
            'colourCode1' => null === $colour ? null : $colour->getCode(),
            'colour1' => null === $colour ? null : $colour->getName(),
            'colourCode2' => null === $colour2 ? null : $colour2->getCode(),
            'colour2' => null === $colour2 ? null : $colour2->getName(),
            'odometer' => $motTest->getOdometerValue(),
            'odometerUnit' => $motTest->getOdometerUnit(),
            'testNumber' => $motTest->getNumber(),
            'testDate' => $this->returnFormattedDateOrNull($motTest->getIssuedDate()),
            'expiryDate' => $this->returnFormattedDateOrNull($motTest->getExpiryDate()),
            'vtsNumber' => $motTest->getVehicleTestingStation()->getSiteNumber(),
            'vtsTelNo' => $this->extractPhoneNumber($motTest->getVehicleTestingStation()),
        ];
    }

    /**
     * @param DvlaVehicle $vehicle
     * @param string|null $primaryColourName
     * @param string|null $secondaryColourName
     * @param string|null $dvlaMakeName
     * @param string|null $dvlaModelName
     *
     * @return array
     */
    public function pre1960VehicleWithNoMotTestToArray(
        DvlaVehicle $vehicle,
        $primaryColourName,
        $secondaryColourName,
        $dvlaMakeName,
        $dvlaModelName
    ) {
        $colour = $vehicle->getPrimaryColour();
        $colour2 = $vehicle->getSecondaryColour();

        return [
            'vrm' => $vehicle->getRegistration(),
            'make' => $dvlaMakeName,
            'model' => $dvlaModelName,
            'colourCode1' => $colour,
            'colour1' => $primaryColourName,
            'colourCode2' => $colour2,
            'colour2' => $secondaryColourName,
            'odometer' => 1960,
            'odometerUnit' => 'M',
            'testNumber' => '196019601960',
            'testDate' => date('Y-01-01'),
            'expiryDate' => date('Y-01-01', strtotime('+1 year')),
            'vtsNumber' => 'PRE1960',
            'vtsTelNo' => 'PRE1960',
        ];
    }
}
