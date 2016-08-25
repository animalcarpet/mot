<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\CardOrderReport\Action;

use Core\Action\NotFoundActionResult;
use Core\Service\MotFrontendAuthorisationServiceInterface;
use Dvsa\Mot\ApiClient\Resource\Collection;
use Dvsa\Mot\ApiClient\Resource\Item\SecurityCardDailyCount;
use Dvsa\Mot\ApiClient\Service\AuthorisationService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrderReport\Action\CardOrderReportListAction;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommonTest\Date\TestDateTimeHolder;
use DvsaCommonTest\TestUtils\XMock;
use DvsaFeature\FeatureToggles;
use \DateTime;
use \stdClass;
use PHPUnit_Framework_TestCase;
use DateInterval;

class CardOrderReportListActionTest extends PHPUnit_Framework_TestCase
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

    /** @var TestDateTimeHolder $testDateTimeHolder */
    private $testDateTimeHolder;

    public function setUp()
    {
        $this->authService = XMock::of(MotFrontendAuthorisationServiceInterface::class);
        $this->authorisationServiceClient = XMock::of(AuthorisationService::class);
        $this->featureToggle = new TwoFaFeatureToggle(new FeatureToggles([FeatureToggle::TWO_FA => true]));
        $this->testDateTimeHolder = XMock::of(TestDateTimeHolder::class);
    }

    public function testExecute_whenFeatureToggleDisabled_shouldReturn404()
    {
        $this->withFeatureToggleDisabled();

        $this->assertInstanceOf(NotFoundActionResult::class, $this->buildCardOrderReportListAction()->execute());
    }

    public function testExecute_whenPermissionNotGranted_shouldThrowUnauthorisedException()
    {
        $this
            ->withViewSecurityCardPermissionNotGranted()
            ->setExpectedException(UnauthorisedException::class);

        $this->buildCardOrderReportListAction()->execute();
    }

    public function testExecute_whenOrderCountsAreRequested_shouldRequestLastSevenDays_whenAfter10AM()
    {

        $currentDateTime = DateTime::createFromFormat('Y-m-d H:i:s', '2009-02-15 13:00:00');
        $currentDate = DateTime::createFromFormat('Y-m-d', '2009-02-15')->setTime(0,0,0);

        $rangeFrom = DateTime::createFromFormat('Y-m-d H:i:s', '2009-02-08 10:00:00');
        $rangeTo = DateTime::createFromFormat('Y-m-d H:i:s', '2009-02-15 10:00:00');

        $this->testDateTimeHolder
            ->method('getUserCurrent')
            ->willReturn($currentDateTime);

        $this->testDateTimeHolder
            ->method('getUserCurrentDate')
            ->willReturn($currentDate);

        $this->withDailyCountCollection($this->buildDailyCountCollection([]));

        $this->authorisationServiceClient
            ->method('getSecurityCardOrderCount')
            ->with($rangeFrom, $rangeTo);

        $this->buildCardOrderReportListAction()->execute();
    }

    public function testExecute_whenOrderCountsAreRequested_shouldRequestLastSevenDays_whenBefore10AM()
    {
        $currentDateTime = DateTime::createFromFormat('Y-m-d H:i:s', '2009-02-15 09:00:00');
        $currentDate = DateTime::createFromFormat('Y-m-d', '2009-02-15')->setTime(0,0,0);

        $rangeFrom = DateTime::createFromFormat('Y-m-d H:i:s', '2009-02-07 10:00:00');
        $rangeTo = DateTime::createFromFormat('Y-m-d H:i:s', '2009-02-14 10:00:00');

        $this->testDateTimeHolder
            ->method('getUserCurrent')
            ->willReturn($currentDateTime);

        $this->testDateTimeHolder
            ->method('getUserCurrentDate')
            ->willReturn($currentDate);

        $this->withDailyCountCollection($this->buildDailyCountCollection([]));

        $this->authorisationServiceClient
            ->method('getSecurityCardOrderCount')
            ->with($rangeFrom, $rangeTo);

        $this->buildCardOrderReportListAction()->execute();
    }

    public function testExecute_whenOrderReportIsRequested_shouldMapDailyCounts()
    {
        $currentDateTime = DateTime::createFromFormat('Y-m-d H:i:s', '2009-02-15 09:00:00');
        $currentDate = DateTime::createFromFormat('Y-m-d', '2009-02-15')->setTime(0,0,0);

        $this->testDateTimeHolder
            ->method('getUserCurrent')
            ->willReturn($currentDateTime);

        $this->testDateTimeHolder
            ->method('getUserCurrentDate')
            ->willReturn($currentDate);

        $dailyCountCollection = $this->buildDailyCountCollection([
            '2016-01-01' => 1,
            '2016-01-02' => 2,
            '2016-01-03' => 15,
            '2016-01-04' => 9,
            '2016-01-05' => 7,
            '2016-01-06' => 22,
            '2016-01-07' => 4,
        ]);
        $this->withDailyCountCollection($dailyCountCollection);

        $result = $this->buildCardOrderReportListAction()->execute();
        $dailyCountRows = $result->getViewModel()['rows'];

        $this->assertEquals($dailyCountCollection->getCount(), count($dailyCountRows));

        for ($i = 0; $i < $dailyCountCollection->getCount(); $i++) {
            $actDate = new \DateTime($dailyCountCollection->getItem($i)->getActivationDate());
            $formattedActivationDate = $actDate->format(DateUtils::DATETIME_FORMAT);

            $this->assertEquals($formattedActivationDate, $dailyCountRows[$i]['date']);
            $this->assertEquals($dailyCountCollection->getItem($i)->getCount(), $dailyCountRows[$i]['count']);
        }
    }

    /**
     * @param array $rawItems
     * @return Collection
     */
    private function buildDailyCountCollection(array $rawItems)
    {
        $dailyCountItems = [];

        foreach ($rawItems as $date => $count) {
            $item = new stdClass();
            $item->date = $date;
            $item->count = $count;

            $dailyCountItems[] = $item;
        }

        return new Collection($dailyCountItems, SecurityCardDailyCount::class);
    }

    /**
     * @param Collection $collection
     * @return $this
     */
    private function withDailyCountCollection(Collection $collection)
    {
        $this->authorisationServiceClient
            ->method('getSecurityCardOrderCount')
            ->willReturn($collection);

        return $this;
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
     * @return CardOrderReportListAction
     */
    private function buildCardOrderReportListAction()
    {
        return new CardOrderReportListAction($this->authService,
            $this->authorisationServiceClient, $this->featureToggle, $this->testDateTimeHolder);
    }
}
