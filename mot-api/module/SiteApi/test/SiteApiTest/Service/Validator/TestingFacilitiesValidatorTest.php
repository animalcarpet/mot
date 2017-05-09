<?php

namespace SiteApiTest\Service\Validator;

use DvsaCommon\Dto\Site\FacilityDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommonApi\Service\Exception\BadRequestException;
use SiteApi\Service\Validator\TestingFacilitiesValidator;

class TestingFacilitiesValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var VehicleTestingStationDto
     */
    protected $vtsDto;

    /**
     * @var TestingFacilitiesValidator
     */
    protected $validator;

    protected function setUp()
    {
        parent::setUp();
        $this->vtsDto = new VehicleTestingStationDto();
        $this->validator = new TestingFacilitiesValidator();
    }

    public function testValidatePasses()
    {
        $this->vtsDto
            ->setIsOptlSelected(true)
            ->setIsTptlSelected(true)
            ->setFacilities(new FacilityDto());

        $this->validator->validate($this->vtsDto);
        $this->assertInstanceOf('\DvsaCommonApi\Service\Validator\ErrorSchema', $this->validator->getErrors());
        $this->assertFalse($this->validator->getErrors()->hasErrors());
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Validation errors encountered
     */
    public function testOptlNotSelectedRaisesError()
    {
        $this->vtsDto
            ->setIsOptlSelected(false)
            ->setIsTptlSelected(true)
            ->setFacilities(new FacilityDto());

        $this->validator->validate($this->vtsDto);
    }

    public function testOtplNotSelectedErrorMessage()
    {
        $this->vtsDto
            ->setIsOptlSelected(false)
            ->setIsTptlSelected(true)
            ->setFacilities(new FacilityDto());

        try {
            $this->validator->validate($this->vtsDto);
        } catch (BadRequestException $e) {
            $errors = $e->getErrors();
            $this->assertEquals('A number of OPTL must be selected', $errors[0]['message']);
            $this->assertEquals(60, $errors[0]['code']);
            $this->assertEquals('A number of OPTL must be selected', $errors[0]['displayMessage']);
            $this->assertEquals('facilityOptl', $errors[0]['field']);
        }
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Validation errors encountered
     */
    public function testTptlNotSelectedRaisesError()
    {
        $this->vtsDto
            ->setIsOptlSelected(true)
            ->setIsTptlSelected(false)
            ->setFacilities(new FacilityDto());

        $this->validator->validate($this->vtsDto);
    }

    public function testTptlNotSelectedErrorMessage()
    {
        $this->vtsDto
            ->setIsOptlSelected(true)
            ->setIsTptlSelected(false)
            ->setFacilities(new FacilityDto());

        try {
            $this->validator->validate($this->vtsDto);
        } catch (BadRequestException $e) {
            $errors = $e->getErrors();
            $this->assertEquals('A number of TPTL must be selected', $errors[0]['message']);
            $this->assertEquals(60, $errors[0]['code']);
            $this->assertEquals('A number of TPTL must be selected', $errors[0]['displayMessage']);
            $this->assertEquals('facilityTptl', $errors[0]['field']);
        }
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Validation errors encountered
     */
    public function testValidateWithNoFacilitiesSet()
    {
        $this->vtsDto
            ->setIsTptlSelected(true)
            ->setIsOptlSelected(true)
            ->setFacilities(null);

        $this->validator->validate($this->vtsDto);
    }

    public function testValidateWithNoFacilitiesSetErrorMessage()
    {
        $this->vtsDto
            ->setIsTptlSelected(true)
            ->setIsOptlSelected(true)
            ->setFacilities(null);

        try {
            $this->validator->validate($this->vtsDto);
        } catch (BadRequestException $e) {
            $errors = $e->getErrors();
            $this->assertEquals('A number for either OPTL or TPTL must be selected', $errors[0]['message']);
            $this->assertEquals(60, $errors[0]['code']);
            $this->assertEquals('A number for either OPTL or TPTL must be selected', $errors[0]['displayMessage']);
            $this->assertEquals('facilityOptl', $errors[0]['field']);
        }
    }
}
