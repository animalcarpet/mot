<?php

namespace DvsaCommonApiTest\Service\Validator;

use DvsaCommonApi\Service\Validator\DrivingLicenceValidator;
use PHPUnit_Framework_TestCase;

/**
 * Class DrivingLicenceValidatorTest.
 */
class DrivingLicenceValidatorTest extends PHPUnit_Framework_TestCase
{
    public function testValidateValidForUnitedKingdomShouldBeOk()
    {
        $this->runTestValidate('MOHAM809292F99YH26');
    }

    public function testValidateValidForNorthernIrelandShouldBeOk()
    {
        $this->runTestValidate('12345678');
    }

    public function testValidateValidForNonUkShouldBeOk()
    {
        $this->runTestValidate('POL123345', 'other');
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\RequiredFieldException
     * @expectedExceptionMessage A required field is missing
     */
    public function testDrivingLicenceNumberNotSelected()
    {
        $this->runTestValidate('');
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\RequiredFieldException
     * @expectedExceptionMessage A required field is missing
     */
    public function testDrivingLicenceNumberNotSelectedWithTypeOther()
    {
        $this->runTestValidate('', 'other');
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\RequiredFieldException
     * @expectedExceptionMessage A required field is missing
     */
    public function testDrivingLicenceRegionNotSelectedWithUkDrivingLicenceNumber()
    {
        $this->runTestValidate('12345678', '');
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\RequiredFieldException
     * @expectedExceptionMessage A required field is missing
     */
    public function testDrivingLicenceRegionNotSelectedWithOtherDrivingLicenceNumber()
    {
        $this->runTestValidate('CAT3456779', '');
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\RequiredFieldException
     * @expectedExceptionMessage A required field is missing
     */
    public function testDrivingLicenceNumberInvalidForNonUk()
    {
        $this->runTestValidate('', 'other');
    }

    private function runTestValidate(
        $drivingLicenceNumber,
        $drivingLicenceRegion = DrivingLicenceValidator::TYPE_DRIVING_LICENCE_UK
    ) {
        $validator = $this->getDrivingLicenceValidator();

        $data = ['drivingLicenceNumber' => $drivingLicenceNumber, 'drivingLicenceRegion' => $drivingLicenceRegion];

        $validator->validate($data);
    }

    /**
     * @return DrivingLicenceValidator
     */
    private function getDrivingLicenceValidator()
    {
        return new DrivingLicenceValidator();
    }
}
