<?php

namespace DvsaMotEnforcementTest\Model;

use Application\Service\CatalogService;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommonTest\TestUtils\XMock;
use InvalidArgumentException;
use PHPUnit_Framework_TestCase;

/**
 * Class MotTestTest.
 */
class MotTestTest extends PHPUnit_Framework_TestCase
{
    public $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new \DvsaMotEnforcement\Model\MotTest();
    }

    public function testTranslateMotTestStatusForDisplay()
    {
        $input = [
            'FAILED' => 'FAIL',
            'PASSED' => 'PASS',
            'ACTIVE' => 'IN PROGRESS',
            'UNKNOWN' => 'UNKNOWN',
        ];

        foreach ($input as $status => $expectedOutput) {
            $this->assertEquals($expectedOutput, $this->model->translateMotTestStatusForDisplay($status));
        }

        $this->assertNull($this->model->translateMotTestStatusForDisplay(null));
        $this->assertEmpty($this->model->translateMotTestStatusForDisplay(''));
        $this->assertEquals([], $this->model->translateMotTestStatusForDisplay([]));
    }

    public function testPrepareDataForVehicleExaminerListRecentMotTestsView()
    {
        $viewRendererMock = XMock::of(\Zend\View\Renderer\PhpRenderer::class);
        $catalogMock = XMock::of(CatalogService::class);

        $data = $this->getTestMotData();
        $preparedData = $this->model->prepareDataForVehicleExaminerListRecentMotTestsView(
            [$data], $viewRendererMock, $catalogMock
        );
        $preparedData = array_pop($preparedData);

        $this->assertEquals('FAIL', $preparedData['display_status']);
        $this->assertEquals('2014-02-05T11:47:00Z', $preparedData['test_date']);
        $this->assertEquals('5 Feb 2014, 11:47am', $preparedData['display_date']);
    }

    public function testPrepareDataForVehicleExamimerListRecentMotTestsViewThrowsExceptionWithStringInput()
    {
        $this->setExpectedException(InvalidArgumentException::class, 'inputData should be an array');

        $viewRendererMock = XMock::of(\Zend\View\Renderer\PhpRenderer::class);
        $catalogMock = XMock::of(CatalogService::class);

        $this->model->prepareDataForVehicleExaminerListRecentMotTestsView(
            'string', $viewRendererMock, $catalogMock
        );
    }

    public function testPrepareDataForVehicleExaminmerListRecentMotTestsViewThrowsExceptionWithObjectInput()
    {
        $this->setExpectedException(InvalidArgumentException::class, 'inputData should be an array');

        $viewRendererMock = XMock::of(\Zend\View\Renderer\PhpRenderer::class);
        $catalogMock = XMock::of(CatalogService::class);

        $this->model->prepareDataForVehicleExaminerListRecentMotTestsView(
            (object) 'thing', $viewRendererMock, $catalogMock
        );
    }

    protected function getTestMotData()
    {
        $vehicleData = [
            'id' => 1,
            'registration' => 'ELFA 1111',
            'vin' => '1M2GDM9AXKP042725',
            'vehicle_class' => '4',
            'make' => 'Volvo',
            'model' => 'S80 GTX',
            'year' => 2011,
            'colour' => 'Black',
            'fuel_type' => 'X',
        ];
        $vehicleTestStation = [
            'id' => '1',
            'siteNumber' => 'V12345',
            'authorisedExaminerId' => 1,
            'name' => 'Example Name',
            'address' => '1 road name, town, postcode',
        ];
        $motTest = [
            'id' => 1,
            'status' => 'FAILED',
            'vehicle' => $vehicleData,
            'vehicleTestingStation' => $vehicleTestStation,
            'startedDate' => '2014-02-05T10:28:00Z',
            'completedDate' => '2014-02-05T11:47:34Z',
            'testDate' => '2014-02-05T11:47:00Z',
            'odometerValue' => '1234',
            'odometerUnit' => 'Km',
            'reasons_for_rejection' => [['rfr-id' => 1], ['rfr-id' => 2]],
            'break_test_results' => [['break-result-id' => 1]],
            'hasRegistration' => true,
            'testType' => MotTestTypeCode::NORMAL_TEST,
        ];

        return $motTest;
    }
}
