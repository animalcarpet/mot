<?php

namespace DvsaMotApiTest\Service\Validator;

use DvsaCommon\Enum\FuelTypeId;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Messages\Vehicle\CreateVehicleErrors;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApi\Service\Exception\RequiredFieldException;
use DvsaMotApi\Service\Validator\VehicleValidator;
use PHPUnit_Framework_TestCase;

/**
 * Class VehicleValidatorTest
 */
class VehicleValidatorTest extends PHPUnit_Framework_TestCase
{
    public function testValidateCorrectDataDoesNotThrowException()
    {
        $data = $this->getCorrectData();

        $this->callValidator($data);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\RequiredFieldException
     */
    public function testValidateMissingRequiredFieldsThrowsException()
    {
        $data = ['fuelTypeId' => FuelTypeId::ELECTRIC];

        $this->callValidator($data);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testValidateIncorrectDateOfFirstUseThrowsException()
    {
        $data = $this->getCorrectData();
        $data = array_merge(
            $data,
            [
                'dateOfFirstUse' => '2000-03-1x',
            ]
        );

        $this->callValidator($data);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testValidateCylinderCapacityNotANumberThrowsException()
    {
        $data = $this->getCorrectData();
        $data = array_merge(
            $data,
            [
                'cylinderCapacity' => 'cc',
            ]
        );

        $this->callValidator($data);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testValidateCylinderCapacityNotInAllowedRangeThrowsException()
    {
        $data = $this->getCorrectData();
        $data = array_merge(
            $data,
            [
                'cylinderCapacity' => '100000',
            ]
        );

        $this->callValidator($data);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testValidateCylinderCapacityCannotBeANonInteger()
    {
        $data = array_merge($this->getCorrectData(), ['cylinderCapacity' => '10.2',]);

        $this->callValidator($data);
    }

    public function testValidateVinCannotBeTooLong()
    {
        try {
            $data = array_merge(
                $this->getCorrectData(),
                ['vin' => str_repeat('x', VehicleValidator::LIMIT_VIN_MAX + 1)]
            );
            $this->callValidator($data);
        } catch (BadRequestException $e) {
            $this->assertEquals(1, count($e->getErrors()));
            $this->assertEquals(sprintf(CreateVehicleErrors::VIN_LENGTH, 1, VehicleValidator::LIMIT_VIN_MAX),
                $e->getErrors()[0]['message']);
        } catch (\Exception $e) {
            $this->fail("Invalid exception thrown");
        }
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testValidateVinCannotBeTooShort()
    {
        $data = array_merge(
            $this->getCorrectData(),
            ['vin' => null]
        );
        $a = $this->callValidator($data);

    }

    public function testValidateRegistrationNumberCannotBeTooLong()
    {
        try {
            $data = array_merge(
                $this->getCorrectData(),
                ['registrationNumber' => str_repeat('x', VehicleValidator::LIMIT_REG_MAX + 1)]
            );
            $this->callValidator($data);
        } catch (BadRequestException $e) {
            $this->assertEquals(1, count($e->getErrors()));
            $this->assertEquals(
                sprintf(CreateVehicleErrors::REG_TOO_LONG, VehicleValidator::LIMIT_REG_MAX),
                $e->getErrors()[0]['message']
            );
        } catch (\Exception $e) {
            $this->fail("Invalid exception thrown");
        }
    }

    public function testValidateVinCannotContainNonAlphanumericChars()
    {
        try {
            $data = array_merge(
                $this->getCorrectData(),
                ['vin' => str_repeat('_', VehicleValidator::LIMIT_VIN_MAX)]
            );
            $this->callValidator($data);
        } catch (BadRequestException $e) {
            $this->assertEquals(1, count($e->getErrors()));
            $this->assertEquals(CreateVehicleErrors::VIN_INVALID, $e->getErrors()[0]['message']);
        } catch (\Exception $e) {
            $this->fail("Invalid exception thrown");
        }
    }

    public function testValidateRegistrationNumberCannotContainNonAlphanumericChars()
    {
        try {
            $data = array_merge($this->getCorrectData(), ['registrationNumber' => '123 abc']);
            $this->callValidator($data);
        } catch (BadRequestException $e) {
            $this->assertEquals(1, count($e->getErrors()));
            $this->assertEquals(CreateVehicleErrors::REG_INVALID, $e->getErrors()[0]['message']);
        } catch (\Exception $e) {
            $this->fail("Invalid exception thrown");
        }
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testValidateInvalidVehicleClass()
    {
        $data = $this->getCorrectData();
        $data['testClass'] = '15';

        $this->callValidator($data);
    }

    /**
     * @dataProvider getCreateRequiredFields
     * @expectedException \DvsaCommonApi\Service\Exception\RequiredFieldException
     */
    public function testRequiredFields($requiredField)
    {
        $data = $this->getCorrectData();
        unset($data[$requiredField]);

        $this->callValidator($data);
    }

    /**
     * @param $data
     */
    private function callValidator($data)
    {
        $validator = new VehicleValidator();
        $validator->validate($data);
    }

    /**
     * @return array
     */
    private function getCorrectData()
    {
        return [
            'registrationNumber' => 'reg',
            'vin' => '1234567890qwertyu',
            'make' => '1',
            'makeOther' => '',
            'model' => '2',
            'modelOther' => '',
            'modelType' => '3',
            'colour' => '4',
            'secondaryColour' => 'X',
            'dateOfFirstUse' => '2014-02-15',
            'testClass' => VehicleClassCode::CLASS_4,
            'countryOfRegistration' => '5',
            'cylinderCapacity' => '60',
            'transmissionType' => '1',
            'fuelTypeId' => FuelTypeId::DIESEL,
            'vtsId' => 10,
        ];
    }

    public function getCreateRequiredFields()
    {
        $requiredFields = [
            'make',
            'model',
            'colour',
            'secondaryColour',
            'dateOfFirstUse',
            'testClass',
            'countryOfRegistration',
            'transmissionType',
            'cylinderCapacity',
        ];

        return array_map(
            function ($x) {
                return [$x];
            },
            $requiredFields
        );
    }
}
