<?php

namespace DvsaMotTestTest\Controller;

use CoreTest\Controller\AbstractFrontendControllerTestCase;
use Dvsa\Mot\Frontend\MotTestModule\Controller\OdometerController;
use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\Constants\OdometerUnit;
use DvsaCommon\UrlBuilder\MotTestUrlBuilder;
use DvsaCommonTest\Bootstrap;
use Zend\Http\PhpEnvironment\Request;
use Zend\Session\Container;

/**
 * Class OdometerControllerTest
 */
class OdometerControllerTest extends AbstractFrontendControllerTestCase
{
    protected function setUp()
    {
        $this->serviceManager = Bootstrap::getServiceManager();
        $this->serviceManager->setAllowOverride(true);
        $this->setServiceManager($this->serviceManager);

        $this->setController(new OdometerController());
        $this->getController()->setServiceLocator($this->serviceManager);

        parent::setUp();
    }

    /**
     * @param $odometerReadingValidData
     *
     * @dataProvider testValidOdometerReadingDataProvider
     */
    public function testMotTestUpdateOdometerWithValidData($odometerReadingValidData)
    {
        $this->markTestSkipped('Due to the route being removed when the FT is off this test is temporarily disabled ' .
            'until the base test class supports FT awareness at Module bootstrap level.');

        $motTestNumber = 1;

        $restClientMock = $this->getRestClientMockForServiceManager();
        $restClientMock
            ->expects($this->any())
            ->method('post')
            ->with(MotTestUrlBuilder::odometerReading($motTestNumber)->toString(), $odometerReadingValidData);

        $this->getResultForAction2('post', 'index', ['tID' => $motTestNumber], null, $odometerReadingValidData);

        $this->assertResponseStatus(self::HTTP_REDIRECT_CODE);
    }

    /**
     * @param $odometerReadingInvalidData
     * @param $flashMessage
     *
     * @dataProvider testInvalidOdometerReadingDataProvider
     */
    public function testMotTestUpdateOdometerWithInvalidData_shouldFlashErrorAndRedirect($odometerReadingInvalidData, $flashMessage)
    {
        $this->markTestSkipped('Due to the route being removed when the FT is off this test is temporarily disabled ' .
            'until the base test class supports FT awareness at Module bootstrap level.');

        $motTestNumber = 1;

        $this->getFlashMessengerMockForAddErrorMessage($flashMessage);

        $this->getResultForAction2('post', 'index', ['tID' => $motTestNumber], null, $odometerReadingInvalidData);

        $this->assertResponseStatus(self::HTTP_REDIRECT_CODE);
    }

    public function testValidOdometerReadingDataProvider()
    {
        return [
            ['odometer' => '1000', 'unit' => OdometerUnit::MILES, 'resultType' => OdometerReadingResultType::OK],
            ['odometer' => '0', 'unit' => OdometerUnit::MILES, 'resultType' => OdometerReadingResultType::OK],
            ['odometer' => '999999', 'unit' => OdometerUnit::MILES, 'resultType' => OdometerReadingResultType::OK],
            ['odometer' => '2000', 'unit' => OdometerUnit::KILOMETERS, 'resultType' => OdometerReadingResultType::OK],
            ['odometer' => '', 'unit' => OdometerUnit::MILES, 'resultType' => OdometerReadingResultType::NOT_READABLE],
            ['odometer' => '', 'unit' => OdometerUnit::MILES, 'resultType' => OdometerReadingResultType::NO_ODOMETER],
        ];
    }

    public function testInvalidOdometerReadingDataProvider()
    {
        return [
            [
                ['odometer' => '', 'unit' => OdometerUnit::MILES, 'resultType' => OdometerReadingResultType::OK],
                'Odometer value must be entered to update odometer reading'
            ],
            [
                ['odometer' => 'ABC', 'unit' => OdometerUnit::MILES, 'resultType' => OdometerReadingResultType::OK],
                'The odometer reading should be a valid number between 0 and 999,999'
            ],
            [
                ['odometer' => '100.5', 'unit' => OdometerUnit::MILES, 'resultType' => OdometerReadingResultType::OK],
                'The odometer reading should be a valid number between 0 and 999,999'
            ],
            [
                ['odometer' => '1234567', 'unit' => OdometerUnit::MILES, 'resultType' => OdometerReadingResultType::OK],
                'The odometer reading should be a valid number between 0 and 999,999'
            ]
        ];
    }
}
