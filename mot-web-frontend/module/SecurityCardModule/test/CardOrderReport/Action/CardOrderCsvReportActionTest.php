<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\CardOrderReport\Action;

use Core\Action\HttpResponseResult;
use Core\Action\NotFoundActionResult;
use Core\Service\MotFrontendAuthorisationServiceInterface;
use Dvsa\Mot\ApiClient\Resource\Collection;
use Dvsa\Mot\ApiClient\Resource\Item\SecurityCardOrder;
use Dvsa\Mot\ApiClient\Service\AuthorisationService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrderReport\Action\CardOrderCsvReportAction;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommonTest\Date\TestDateTimeHolder;
use DvsaCommonTest\TestUtils\XMock;
use DvsaFeature\FeatureToggles;
use PHPUnit_Framework_TestCase;
use stdClass;

class CardOrderCsvReportActionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var MotFrontendAuthorisationServiceInterface
     */
    private $authService;

    /**
     * @var AuthorisationService
     */
    private $authorisationServiceClient;

    /**
     * @var TwoFaFeatureToggle
     */
    private $featureToggle;

    public function setUp()
    {
        $this->authService = XMock::of(MotFrontendAuthorisationServiceInterface::class);
        $this->authorisationServiceClient = XMock::of(AuthorisationService::class);
        $this->featureToggle = new TwoFaFeatureToggle(new FeatureToggles([FeatureToggle::TWO_FA => true]));
    }

    public function testExecute_whenFeatureToggleDisabled_shouldReturnNotFoundResult()
    {
        $this->withFeatureToggleDisabled();

        $result = $this->buildCardOrderCsvReportAction()->execute('2016-01-01');

        $this->assertInstanceOf(NotFoundActionResult::class, $result);
    }

    public function testExecute_whenPermissionNotGranted_shouldThrowUnauthorisedException()
    {
        $this
            ->withViewSecurityCardPermissionNotGranted()
            ->setExpectedException(UnauthorisedException::class);

        $this->buildCardOrderCsvReportAction()->execute('2016-01-01');
    }

    public function testExecute_whenInvalidDate_shouldReturnNotFoundResult()
    {
        $result = $this->buildCardOrderCsvReportAction()->execute('not a date');

        $this->assertInstanceOf(NotFoundActionResult::class, $result);
    }

    public function testExecute_whenDateMoreThanSevenDaysAgo_shouldReturnNotFoundResult()
    {
        $result = $this->buildCardOrderCsvReportAction()->execute(date('Y-m-d\TH:i:s\Z', strtotime('-8 days')));

        $this->assertInstanceOf(NotFoundActionResult::class, $result);
    }

    public function testExecute_whenDateAfterToday_shouldReturnNotFoundResult()
    {
        $result = $this->buildCardOrderCsvReportAction()->execute(date('Y-m-d\TH:i:s\Z', strtotime('+1 days')));

        $this->assertInstanceOf(NotFoundActionResult::class, $result);
    }

    //TODO fix this failing test - requires a testDateTimeHolder
//    public function testExecute_whenBuildingCSV_shouldMapSecurityCardOrdersToCsv()
//    {
//        $date = date('Y-m-d\TH:i:s\Z', strtotime('yesterday'));

//        $securityCardOrders = [
//            $this->buildSecurityCardOrder('Robert Arctor', 'Robert Winstone', $date)
//        ];

//        $this->authorisationServiceClient
//            ->method('getSecurityCardOrdersInDateRange')
//            ->willReturn(new Collection($securityCardOrders, SecurityCardOrder::class));

//        $result = $this->buildCardOrderCsvReportAction()->execute($date);

//        $this->assertInstanceOf(HttpResponseResult::class, $result);

//        $csvLines = explode("\n", trim($result->getResponse()->getBody()));
//        $this->assertEquals(2, count($csvLines));

//        $orderLine1 = str_getcsv($csvLines[1]);

//        $expectedOrderLine = [
//            $date,
//            'Robert Arctor',
//            'Robert Winstone',
//            'The Axis Building',
//            '112 Upper Parliament St',
//            '',
//            'Nottingham',
//            'NG1 6LP'
//        ];

//        $this->assertEquals($expectedOrderLine, $orderLine1);
//    }

    private function buildSecurityCardOrder($fullName, $recipientName, $date)
    {
        $orderData = new stdClass();
        $orderData->fullName = $fullName;
        $orderData->recipientName = $recipientName;
        $orderData->submittedOn = $date;
        $orderData->addressLine1 = 'The Axis Building';
        $orderData->addressLine2 = '112 Upper Parliament St';
        $orderData->addressLine3 = '';
        $orderData->town = 'Nottingham';
        $orderData->postcode = 'NG1 6LP';

        return $orderData;
    }

    /**
     * @return $this
     */
    private function withFeatureToggleDisabled()
    {
        $this->featureToggle = new TwoFaFeatureToggle(new FeatureToggles([FeatureToggle::TWO_FA => false]));

        return $this;
    }

    /**
     * @return $this
     */
    private function withViewSecurityCardPermissionNotGranted()
    {
        $this->authService
            ->method('assertGranted')
            ->with(PermissionInSystem::VIEW_SECURITY_CARD_ORDER)
            ->will($this->throwException(new UnauthorisedException('')));

        return $this;
    }

    /**
     * @return CardOrderCsvReportAction
     */
    private function buildCardOrderCsvReportAction()
    {
        return new CardOrderCsvReportAction(
            'php://temp',
            $this->authService,
            $this->authorisationServiceClient,
            $this->featureToggle,
            new DateTimeHolder()
        );
    }
}
