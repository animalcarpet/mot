<?php

namespace DvsaMotTestTest\View\VehicleSearchResult;

use DvsaCommonTest\TestUtils\XMock;
use DvsaMotTest\Controller\StartTestConfirmationController;
use DvsaMotTest\Model\VehicleSearchResult;
use DvsaMotTest\View\VehicleSearchResult\NonMotTestUrlTemplate;
use Zend\Mvc\Controller\Plugin\Url;

class NonMotTestUrlTemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var int
     */
    private $noRegistration;
    /**
     * @var Url | \PHPUnit_Framework_MockObject_MockObject
     */
    private $urlHelper;
    /**
     * @var NonMotTestUrlTemplate
     */
    private $nonMotTestUrlTemplate;
    /**
     * @var VehicleSearchResult | \PHPUnit_Framework_MockObject_MockObject
     */
    private $vehicleSearchResults;

    public function setUp()
    {
        $this->urlHelper = XMock::of(Url::class);
        $this->vehicleSearchResults = XMock::of(VehicleSearchResult::class);
        $this->noRegistration = 0;
        $this->nonMotTestUrlTemplate = new NonMotTestUrlTemplate(
            $this->noRegistration,
            $this->urlHelper
        );
    }

    /**
     * @param array $params
     * @expectedException \OutOfBoundsException
     * @dataProvider missingParamProvider
     */
    public function testGetUrl_withMissingParams_shouldThrow(array $params)
    {
        $this->nonMotTestUrlTemplate->getUrl($params);
    }

    public function missingParamProvider()
    {
        return [
            [
                'params' => [],
            ],
            [
                'params' => [
                  'source' => 'test',
                  'vin' => 'test',
                  'registration' => 'test',
                  'searchVrm' => 'test',
                  'searchVin' => 'test',
                ],
            ],
            [
                'params' => [
                  'id' => 'test',
                  'vin' => 'test',
                  'registration' => 'test',
                  'searchVrm' => 'test',
                  'searchVin' => 'test',
                ],
            ],
            [
                'params' => [
                  'id' => 'test',
                  'source' => 'test',
                  'registration' => '',
                  'searchVrm' => '',
                  'searchVin' => '',
                ],
            ],
            [
                'params' => [
                  'id' => 'test',
                  'source' => 'test',
                  'vin' => 'test',
                  'searchVrm' => 'test',
                  'searchVin' => 'test',
                ],
            ],
            [
                'params' => [
                  'id' => 'test',
                  'source' => 'test',
                  'vin' => 'test',
                  'registration' => 'test',
                  'searchVin' => 'test',
                ],
            ],
            [
                'params' => [
                  'id' => 'test',
                  'source' => 'test',
                  'vin' => 'test',
                  'registration' => 'test',
                  'searchVrm' => 'test',
                ],
            ],
        ];
    }

    /**
     * @param array $params
     * @param int   $noRegistration
     *
     * @internal param string $source
     * @dataProvider validSearchParamsProvider
     */
    public function testGetUrl(array $params, $noRegistration)
    {
        $this->urlHelper
            ->expects($this->once())
            ->method('fromRoute')
            ->with(
                NonMotTestUrlTemplate::START_NON_MOT_TEST_CONFIRMATION_ROUTE,
                [
                    'controller' => NonMotTestUrlTemplate::START_TEST_CONFIRMATION_CONTROLLER,
                    'action' => NonMotTestUrlTemplate::NOT_MOT_TEST_ACTION,
                    StartTestConfirmationController::ROUTE_PARAM_ID => $params['id'],
                    StartTestConfirmationController::ROUTE_PARAM_NO_REG => $noRegistration,
                    StartTestConfirmationController::ROUTE_PARAM_SOURCE => $params['source'],
                ],
                [
                    'query' => [
                        'vin' => $params['vin'],
                        'registration' => $params['registration'],
                        'searchVrm' => $params['searchVrm'],
                        'searchVin' => $params['searchVin'],
                    ],
                ]
            )
        ;

        $this->nonMotTestUrlTemplate->getUrl($params);
    }

    public function validSearchParamsProvider()
    {
        return [
            [
                'params' => [
                    'id' => 'test',
                    'source' => 'test',
                    'vin' => 'test',
                    'registration' => 'test',
                    'searchVin' => 'test',
                    'searchVrm' => 'test',
                ],
                'noRegistration' => 0,
            ],
        ];
    }

    /**
     * @param array $searchParams
     * @dataProvider getStartMotTestUrlProvider
     */
    public function testGetStartMotTestUrl(array $searchParams)
    {
        $this->setUpVehicleSearchResults($searchParams);

        $this->urlHelper
            ->expects($this->once())
            ->method('fromRoute')
            ->with(
                NonMotTestUrlTemplate::START_NON_MOT_TEST_CONFIRMATION_ROUTE,
                [
                    'controller' => NonMotTestUrlTemplate::START_TEST_CONFIRMATION_CONTROLLER,
                    'action' => NonMotTestUrlTemplate::NOT_MOT_TEST_ACTION,
                    StartTestConfirmationController::ROUTE_PARAM_ID => $searchParams['id'],
                    StartTestConfirmationController::ROUTE_PARAM_NO_REG => $this->noRegistration,
                    StartTestConfirmationController::ROUTE_PARAM_SOURCE => $searchParams['source'],
                ],
                [
                    'query' => [
                        'vin' => $searchParams['vin'],
                        'registration' => $searchParams['registration'],
                        'searchVrm' => $searchParams['searchVrm'],
                        'searchVin' => $searchParams['searchVin'],
                    ],
                ]
            );

        $this->nonMotTestUrlTemplate->getStartMotTestUrl($this->vehicleSearchResults, $searchParams);
    }

    public function getStartMotTestUrlProvider()
    {
        return [
          [
              'searchParams' => [
                  'id' => 'test',
                  'source' => 'test',
                  'vin' => 'test',
                  'registration' => 'test',
                  'searchVin' => 'test',
                  'searchVrm' => 'test',
              ],
          ],
        ];
    }

    /**
     * @param array $searchParams
     */
    private function setUpVehicleSearchResults(array $searchParams)
    {
        $this->vehicleSearchResults
            ->method('getId')
            ->willReturn($searchParams['id']);
        $this->vehicleSearchResults
            ->method('getSource')
            ->willReturn($searchParams['source']);
        $this->vehicleSearchResults
            ->method('getRegistrationNumber')
            ->willReturn($searchParams['registration']);
        $this->vehicleSearchResults
            ->method('getVin')
            ->willReturn($searchParams['vin']);
    }
}
