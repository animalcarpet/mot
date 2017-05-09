<?php

namespace DvsaMotTestTest\View\Model;

use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use DvsaCommon\Dto\Common\MotTestTypeDto;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\UrlBuilder\MotTestUrlBuilderWeb;
use DvsaMotTest\View\Model\MotPrintModel;
use DvsaMotTestTest\TestHelper\Fixture;

/**
 * Class MotPrintModelTest.
 */
class MotPrintModelTest extends \PHPUnit_Framework_TestCase
{
    const MOT_TEST_NUMBER = 1;
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
        $data = $this->getMotTestDataDto(null, MotTestStatusName::FAILED, 1);
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
            [(new MotTestTypeDto())->setCode(MotTestTypeCode::STATUTORY_APPEAL), true, true],
        ];
    }

    private function getMotTestDataDto($testType = null, $status = MotTestStatusName::PASSED, $prsMotTestNumber = null)
    {
        if ($testType === null) {
            $testType = (new MotTestTypeDto())->setCode(MotTestTypeCode::NORMAL_TEST);
        }

        $vehicle = new DvsaVehicle(Fixture::getDvsaVehicleTestDataVehicleClass4(true));
        $motTestData = Fixture::getMotTestDataVehicleClass4(true);
        if ($prsMotTestNumber === 1 && $status === MotTestStatusname::FAILED) {
            $motTestData = $this->getMotTestDataClass4Failed($motTestData);
        } elseif ($status === MotTestStatusname::FAILED && $prsMotTestNumber !== null) {
            $motTestData = $this->getMotTestDataClass4WithPrsFailed($motTestData);
        } elseif ($prsMotTestNumber !== null) {
            $motTestData = $this->getMotTestDataClass4WithPrs($motTestData);
        } else {
            $motTestData->status = MotTestStatusName::PASSED;
        }

        if ($testType->getCode() === MotTestTypeCode::RE_TEST) {
            $motTestData = $this->getMotTestDataClass4ReTest($motTestData);
        }

        if ($testType->getCode() === MotTestTypeCode::NON_MOT_TEST) {
            $motTestData = $this->getMotTestDataClass4NonMotTest($motTestData);
        }
        if ($testType->getCode() === MotTestTypeCode::TARGETED_REINSPECTION
            || $testType->getCode() === MotTestTypeCode::MOT_COMPLIANCE_SURVEY
            || $testType->getCode() === MotTestTypeCode::OTHER
            || $testType->getCode() === MotTestTypeCode::INVERTED_APPEAL
            || $testType->getCode() === MotTestTypeCode::STATUTORY_APPEAL) {
            $motTestData = $this->getMotTestNonStandardTypes($motTestData, $testType->getCode());
        }
        $motDetails = new MotTest($motTestData);

        return [
            'motDetails' => $motDetails,
            'vehicle' => $vehicle,
        ];
    }

    private function getMotTestNonStandardTypes($motTestData, $testTypeCode)
    {
        $motTestData->testTypeCode = $testTypeCode;

        return $motTestData;
    }

    private function getMotTestDataClass4NonMotTest($motTestData)
    {
        $motTestData->status = MotTestStatusName::PASSED;
        $motTestData->testTypeCode = MotTestTypeCode::NON_MOT_TEST;

        return $motTestData;
    }

    private function getMotTestDataClass4ReTest($motTestData)
    {
        $motTestData->testTypeCode = MotTestTypeCode::RE_TEST;

        return $motTestData;
    }

    private function getMotTestDataClass4Failed($motTestData)
    {
        $motTestData->status = MotTestStatusName::FAILED;

        return $motTestData;
    }

    private function getMotTestDataClass4WithPrs($motTestData)
    {
        $motTestData->prsMotTestNumber = '999999991234';
        $motTestData->status = MotTestStatusName::PASSED;

        return $motTestData;
    }

    private function getMotTestDataClass4WithPrsFailed($motTestData)
    {
        $motTestData->prsMotTestNumber = '999999991234';
        $motTestData->status = MotTestStatusName::FAILED;

        return $motTestData;
    }
}
