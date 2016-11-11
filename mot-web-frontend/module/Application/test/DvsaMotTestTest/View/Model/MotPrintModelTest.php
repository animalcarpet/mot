<?php
namespace DvsaMotTestTest\View\Model;

use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Dto\Common\MotTestTypeDto;
use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\UrlBuilder\MotTestUrlBuilderWeb;
use DvsaCommon\Utility\ArrayUtils;
use DvsaMotTest\View\Model\MotPrintModel;

/**
 * Class MotPrintModelTest
 *
 */
class MotPrintModelTest extends \PHPUnit_Framework_TestCase
{
    const MOT_TEST_NUMBER = 88888881234;
    const PRS_MOT_TEST_NUMBER = 999999991234;

    public function testBasicPass()
    {
        $data = $this->getMotTestDataDto();
        $object = new MotPrintModel($data);

        $variables = $object->getVariables();

        $this->assertEquals(null, $object->getOptions);

        $this->assertEquals(self::MOT_TEST_NUMBER, $variables['passedMotTestId']);
        $this->assertEquals(null, $variables['failedMotTestId']);

        $this->assertEquals(false, $variables['isNonMotTest']);
        $this->assertEquals(false, $variables['isReinspection']);
        $this->assertEquals(false, $variables['isAppeal']);
        $this->assertEquals(false, $variables['isDuplicate']);

        $this->assertEquals(MotTestUrlBuilderWeb::printCertificate(self::MOT_TEST_NUMBER), $variables['printRoute']);

        $this->assertEquals('MOT test complete', $variables['title']);

        $this->assertEquals('A1 BCD', $variables['vehicleRegistration']);
    }

    public function testBasicPassIsDuplicate()
    {
        $data = $this->getMotTestDataDto();
        $data['isDuplicate'] = true;

        $object = new MotPrintModel($data);

        $variables = $object->getVariables();

        $this->assertEquals(true, $variables['isDuplicate']);
        $this->assertEquals('Duplicate document available', $variables['title']);
        $this->assertEquals(
            MotTestUrlBuilderWeb::printCertificateDuplicate(self::MOT_TEST_NUMBER), $variables['printRoute']
        );
    }

    public function testBasicFail()
    {
        $data = $this->getMotTestDataDto(null, MotTestStatusName::FAILED);
        $object = new MotPrintModel($data);

        $variables = $object->getVariables();

        $this->assertEquals(null, $variables['passedMotTestId']);
        $this->assertEquals(self::MOT_TEST_NUMBER, $variables['failedMotTestId']);
    }

    public function testPassWithPrs()
    {
        $data = $this->getMotTestDataDto(null, MotTestStatusName::PASSED, self::PRS_MOT_TEST_NUMBER);
        $object = new MotPrintModel($data);

        $variables = $object->getVariables();

        $this->assertEquals(self::MOT_TEST_NUMBER, $variables['passedMotTestId']);
        $this->assertEquals(self::PRS_MOT_TEST_NUMBER, $variables['failedMotTestId']);
    }

    public function testFailWithPrs()
    {
        $data = $this->getMotTestDataDto(null, MotTestStatusName::FAILED, self::PRS_MOT_TEST_NUMBER);
        $object = new MotPrintModel($data);

        $variables = $object->getVariables();

        $this->assertEquals(self::PRS_MOT_TEST_NUMBER, $variables['passedMotTestId']);
        $this->assertEquals(self::MOT_TEST_NUMBER, $variables['failedMotTestId']);
    }

    public function testTitleForRetest()
    {
        $data = $this->getMotTestDataDto((new MotTestTypeDto())->setCode(MotTestTypeCode::RE_TEST));
        $object = new MotPrintModel($data);

        $variables = $object->getVariables();

        $this->assertEquals('MOT re-test complete', $variables['title']);
    }

    public function testTitleForNonMotTest()
    {
        $data = $this->getMotTestDataDto((new MotTestTypeDto())->setCode(MotTestTypeCode::NON_MOT_TEST));
        $object = new MotPrintModel($data);

        $variables = $object->getVariables();

        $this->assertEquals('Non-MOT test finished successfully', $variables['title']);
        $this->assertEquals(true, $variables['isNonMotTest']);
    }

    /**
     * @dataProvider typeProvider
     */
    public function testNonStandardTypes($type, $isReinspection, $isAppeal)
    {
        $data = $this->getMotTestDataDto($type);
        $object = new MotPrintModel($data);

        $variables = $object->getVariables();

        $this->assertEquals($isReinspection, $variables['isReinspection']);
        $this->assertEquals($isAppeal, $variables['isAppeal']);
    }

    public function typeProvider()
    {
        return [
            [(new MotTestTypeDto())->setCode(MotTestTypeCode::TARGETED_REINSPECTION), true, false],
            [(new MotTestTypeDto())->setCode(MotTestTypeCode::MOT_COMPLIANCE_SURVEY), true, false],
            [(new MotTestTypeDto())->setCode(MotTestTypeCode::OTHER), true, false],
            [(new MotTestTypeDto())->setCode(MotTestTypeCode::INVERTED_APPEAL), true, true],
            [(new MotTestTypeDto())->setCode(MotTestTypeCode::STATUTORY_APPEAL), true, true]
        ];
    }

    private function getMotTestDataDto($testType = null, $status = MotTestStatusName::PASSED, $prsMotTestNumber = null)
    {
        if ($testType === null) {
            $testType = (new MotTestTypeDto())->setCode(MotTestTypeCode::NORMAL_TEST);
        }

        $vehicle = new VehicleDto();
        $vehicle->setRegistration('A1 BCD');

        $motDetails = (new MotTestDto())
            ->setMotTestNumber(self::MOT_TEST_NUMBER)
            ->setStatus($status)
            ->setPrsMotTestNumber($prsMotTestNumber)
            ->setTestType($testType)
            ->setVehicle($vehicle);

        return [
            'motDetails' => $motDetails
        ];
    }

    protected function getMotTestData($overrides = [])
    {
        $testType = ArrayUtils::tryGet(
            $overrides, 'type', (new MotTestTypeDto())->setCode(MotTestTypeCode::NORMAL_TEST)
        );
        $status = ArrayUtils::tryGet($overrides, 'status', MotTestStatusName::PASSED);
        $prsMotTestNumber = ArrayUtils::tryGet($overrides, 'prsId');

        $vehicle = new VehicleDto();
        $vehicle->setRegistration('A1 BCD');

        $motDetails = [
            'motTestNumber'    => self::MOT_TEST_NUMBER,
            'status'           => $status,
            'prsMotTestNumber' => $prsMotTestNumber,
            'testType'         => $testType,
            'vehicle'          => $vehicle,
        ];

        return [
            'motDetails' => $motDetails
        ];
    }
}
