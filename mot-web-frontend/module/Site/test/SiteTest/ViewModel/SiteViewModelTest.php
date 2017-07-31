<?php

namespace SiteTest\ViewModel;

use DvsaClient\Entity\SiteDailyOpeningHours;
use DvsaCommon\Dto\Equipment\EquipmentDto;
use DvsaCommon\Dto\Equipment\EquipmentModelDto;
use DvsaCommon\Dto\MotTesting\MotTestInProgressDto;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Dto\Site\SiteDto;
use DvsaCommon\Dto\Site\SiteTestingDailyScheduleDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_MockObject_MockObject;
use Site\Authorization\VtsOverviewPagePermissions;
use Site\ViewModel\SiteViewModel;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Zend\Mvc\Controller\Plugin\Url;

/**
 * Class SiteViewModelTest.
 */
class SiteViewModelTest extends \PHPUnit_Framework_TestCase
{
    const STATUS_MODEL = 'good';

    /**
     * @var VtsOverviewPagePermissions|MockObject
     */
    private $permissions;
    /** @var  VehicleTestingStationDto */
    private $site;
    private $equipments;
    private $tests;
    private $model;
    /** @var Url | PHPUnit_Framework_MockObject_MockObject */
    private $urlHelper;

    public function SetUp()
    {
        $this->site = (new VehicleTestingStationDto());
        $this->equipments = [(new EquipmentDto())->setModel((new EquipmentModelDto())->setStatus(self::STATUS_MODEL))];
        $this->tests = [(new MotTestInProgressDto())];
        $this->permissions = XMock::of(VtsOverviewPagePermissions::class);
        $this->model = [self::STATUS_MODEL => 'test'];
        $this->urlHelper = XMock::of(Url::class);
    }

    public function testGetterSetter()
    {
        $model = new SiteViewModel($this->site, $this->equipments, $this->tests, $this->permissions, $this->model, $this->urlHelper, '');

        $this->assertEquals($this->site, $model->getSite());
        $this->assertEquals($this->permissions, $model->getPermissions());
        $this->assertEquals('England', $model->getCountryToggle());

        $model = new SiteViewModel($this->site->setIsScottishBankHoliday(true), $this->equipments, $this->tests, $this->permissions, $this->model, $this->urlHelper, '');
        $this->assertEquals('Scotland', $model->getCountryToggle());
        $model = new SiteViewModel($this->site->setIsDualLanguage(true), $this->equipments, $this->tests, $this->permissions, $this->model, $this->urlHelper, '');
        $this->assertEquals('Wales', $model->getCountryToggle());

        $this->assertEquals(
            SiteDailyOpeningHours::$DAY_NAMES[1],
            $model->displayWeekday((new SiteTestingDailyScheduleDto())->setWeekday(1))
        );

        $this->assertEquals(
            'Closed',
            $model->displayOpeningHours(
                (new SiteTestingDailyScheduleDto())->setOpenTime(null)->setCloseTime(null)
            )
        );
    }

    public function testServiceReportsLink()
    {
        $model = new SiteViewModel(
            $this->site->setOrganisation(new OrganisationDto()),
            $this->equipments, $this->tests, $this->permissions, $this->model, $this->urlHelper, 'service-reports'
        );
        $this->urlHelper->expects($this->once())->method('fromRoute')->willReturn('service_reports');

        $this->assertTrue($model->userCameFromServiceReports());
        $this->assertEquals('service_reports', $model->getBackToServiceReportsLink());
    }

    public function testLackOfServiceReportsLink()
    {
        $model = new SiteViewModel(
            $this->site, $this->equipments, $this->tests, $this->permissions, $this->model, $this->urlHelper, 'whatever'
        );

        $this->assertFalse($model->userCameFromServiceReports());
    }
}
