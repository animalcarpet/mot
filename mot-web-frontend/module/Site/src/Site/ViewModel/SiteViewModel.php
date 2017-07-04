<?php

namespace Site\ViewModel;

use Core\Formatting\AddressFormatter;
use Core\Routing\VtsRoutes;
use Core\ViewModel\Equipment\EquipmentViewModel;
use Core\ViewModel\Gds\Table\GdsTable;
use DvsaClient\Entity\SiteDailyOpeningHours;
use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaCommon\Date\Time;
use DvsaCommon\Dto\Equipment\EquipmentDto;
use DvsaCommon\Dto\MotTesting\MotTestInProgressDto;
use DvsaCommon\Dto\Site\EnforcementSiteAssessmentDto;
use DvsaCommon\Dto\Site\SiteTestingDailyScheduleDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Enum\SiteContactTypeCode;
use DvsaCommon\Enum\SiteTypeCode;
use DvsaCommon\Enum\SiteTypeName;
use DvsaCommon\Model\VtsStatus;
use DvsaCommon\UrlBuilder\AuthorisedExaminerUrlBuilderWeb;
use DvsaCommon\Utility\ArrayUtils;
use Site\Authorization\VtsOverviewPagePermissions;
use Site\UpdateVtsProperty\UpdateVtsPropertyAction;
use Site\ViewModel\MotTest\MotTestInProgressViewModel;
use Zend\Mvc\Controller\Plugin\Url;

/**
 * Class VTSDecorator.
 */
class SiteViewModel
{
    const LIMIT_GREEN_THRESHOLD = 324.1;
    const LIMIT_AMBER_THRESHOLD = 459.2;

    /**
     * @var VehicleTestingStationDto
     */
    private $site;
    /**
     * @var EquipmentViewModel[]
     */
    private $equipments;
    /**
     * @var MotTestInProgressViewModel[]
     */
    private $testsInProgress;

    /**
     * @var VtsOverviewPagePermissions
     */
    private $permissions;
    /**
     * @var Url
     */
    private $urlHelper;

    /**
     * @param VehicleTestingStationDto   $site
     * @param EquipmentDto[]             $equipments
     * @param MotTestInProgressDto[]     $testsInProgress
     * @param VtsOverviewPagePermissions $permissions
     * @param array                      $equipmentModelStatusMap
     * @param Url                        $urlHelper
     */
    public function __construct(
        VehicleTestingStationDto $site,
        $equipments,
        $testsInProgress,
        VtsOverviewPagePermissions $permissions,
        $equipmentModelStatusMap,
        Url $urlHelper
    ) {
        $this->site = $site;
        $this->permissions = $permissions;
        $this->urlHelper = $urlHelper;

        $this->setupEquipment($equipments, $equipmentModelStatusMap);
        $this->wrapTestsInProgress($testsInProgress);
    }

    /**
     * @param EquipmentDto[] $equipmentDto
     * @param $equipmentModelStatusMap
     */
    private function setupEquipment($equipmentDto, $equipmentModelStatusMap)
    {
        $this->equipments = ArrayUtils::map(
            $equipmentDto,
            function (EquipmentDto $equipmentDto) use ($equipmentModelStatusMap) {
                $modelStatus = $equipmentModelStatusMap[$equipmentDto->getModel()->getStatus()];

                return new EquipmentViewModel($equipmentDto, $modelStatus);
            }
        );
    }

    /**
     * @param MotTestInProgressDto[] $testsInProgress
     */
    private function wrapTestsInProgress($testsInProgress)
    {
        $this->testsInProgress = [];
        foreach ($testsInProgress as $testInProgress) {
            $this->testsInProgress[] = new MotTestInProgressViewModel($testInProgress);
        }
    }

    /**
     * @return VehicleTestingStationDto
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @return \Core\ViewModel\Equipment\EquipmentViewModel[]
     */
    public function getEquipments()
    {
        return $this->equipments;
    }

    /**
     * @return MotTest\MotTestInProgressViewModel[]
     */
    public function getTestsInProgress()
    {
        return $this->testsInProgress;
    }

    /**
     * @return VtsOverviewPagePermissions
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    public function getCountryToggle()
    {
        if ($this->site->isDualLanguage() == true) {
            return 'Wales';
        }
        if ($this->site->isScottishBankHoliday() == true) {
            return 'Scotland';
        }

        return 'England';
    }

    public function getCurrentAssessment()
    {
        return (empty($this->site->getCurrentAssessment()))
            ? (new EnforcementSiteAssessmentDto())->setSiteAssessmentScore(0)
            : $this->site->getCurrentAssessment();
    }

    public function getPreviousAssessment()
    {
        return $this->site->getPreviousAssessment();
    }

    public function displayWeekday(SiteTestingDailyScheduleDto $schedule)
    {
        return SiteDailyOpeningHours::$DAY_NAMES[$schedule->getWeekday()];
    }

    public function displayOpeningHours(SiteTestingDailyScheduleDto $schedule)
    {
        if ($schedule->getOpenTime() === null && $schedule->getCloseTime() === null) {
            return 'Closed';
        }

        return DateTimeDisplayFormat::time(Time::fromIso8601($schedule->getOpenTime())).
        ' to '.DateTimeDisplayFormat::time(Time::fromIso8601($schedule->getCloseTime()));
    }

    public function getSiteTypes()
    {
        $types = array_combine(SiteTypeCode::getAll(), SiteTypeName::getAll());

        return ArrayUtils::asortBy($types);
    }

    public function getStatusName($key)
    {
        $statuses = VtsStatus::getStatuses();

        return $statuses[$key];
    }

    public function buildSiteDetailsSummaryTable()
    {
        $permissions = $this->getPermissions();
        $site = $this->getSite();
        $organisation = $site->getOrganisation();
        $organisationDisplay = '';
        if (isset($organisation)) {
            if ($permissions->canViewAuthorisedExaminer()) {
                $organisationDisplay = '<a id="authorised-examiner-link" href="'.AuthorisedExaminerUrlBuilderWeb::of($organisation->getId())->toString().'">'.$organisation->getName().'</a>';
            } else {
                $organisationDisplay = $organisation->getName();
            }
        }

        $table = new GdsTable();
        $row = $table->newRow('site-name')->setLabel('Name')->setValue($site->getName());
        if ($permissions->canChangeSiteName()) {
            $row->addActionLink('Change', VtsRoutes::of($this->urlHelper)->vtsEditProperty($site->getId(), UpdateVtsPropertyAction::VTS_NAME_PROPERTY), 'Change Name');
        }
        $table->newRow('site-number')->setLabel('VTS ID')->setValue($site->getSiteNumber());
        $row = $table->newRow('site-classes')->setLabel('Classes')->setValue(!empty($site->getTestClasses()) ? implode(',', $site->getTestClasses()) : 'None');

        if ($permissions->canChangeSiteClasses()) {
            $row->addActionLink('Change', VtsRoutes::of($this->urlHelper)->vtsEditProperty($site->getId(), UpdateVtsPropertyAction::VTS_CLASSES_PROPERTY), 'Change Classes');
        }

        if ($permissions->canViewVtsType()) {
            $row = $table->newRow('site-type')->setLabel('Type')->setValue($this->getSiteTypes()[$site->getType()]);

            if ($permissions->canChangeSiteType()) {
                $row->addActionLink('Change', VtsRoutes::of($this->urlHelper)->vtsEditProperty($site->getId(), UpdateVtsPropertyAction::VTS_TYPE_PROPERTY), 'Change Type');
            }
        }

        if ($permissions->canViewAuthorisedExaminer()) {
            $table->newRow('authorisedExaminer')->setLabel('Authorised Examiner')->setValue($organisationDisplay, false);
        } else {
            $table->newRow('authorisedExaminer')->setLabel('Authorised Examiner')->setValue($organisationDisplay);
        }

        $row = $table->newRow('site-status')->setLabel('Status')->setValue($this->getStatusName($site->getStatus()));

        if ($permissions->canChangeSiteStatus()) {
            $row->addActionLink('Change', VtsRoutes::of($this->urlHelper)->vtsEditProperty($site->getId(), UpdateVtsPropertyAction::VTS_STATUS_PROPERTY), 'Change Status');
        }

        return $table;
    }

    public function buildSiteContactDetailsSummaryTable()
    {
        $permissions = $this->getPermissions();
        $site = $this->getSite();
        $contact = $site->getContactByType(SiteContactTypeCode::BUSINESS);

        $table = new GdsTable();

        $row = $table->newRow('site-address')->setLabel('Address')->setValue((new AddressFormatter())->escapedDtoToMultiLine($contact->getAddress()), false);

        if ($permissions->canChangeSiteAddress()) {
            $row->addActionLink('Change', VtsRoutes::of($this->urlHelper)->vtsEditProperty($site->getId(), UpdateVtsPropertyAction::VTS_ADDRESS_PROPERTY), 'Change Address');
        }

        if ($permissions->canViewCountry()) {
            $row = $table->newRow('site-country')->setLabel('Country')->setValue($this->getCountryToggle());
            if ($permissions->canChangeSiteCountry()) {
                $row->addActionLink('Change', VtsRoutes::of($this->urlHelper)->vtsEditProperty($site->getId(), UpdateVtsPropertyAction::VTS_COUNTRY_PROPERTY), 'Change Country');
            }
        }

        $row = $table->newRow('email')->setLabel('Email')->setValue($contact->getPrimaryEmailAddress());
        if ($permissions->canChangeSiteEmail()) {
            $row->addActionLink('Change', VtsRoutes::of($this->urlHelper)->vtsEditProperty($site->getId(), UpdateVtsPropertyAction::VTS_EMAIL_PROPERTY), 'Change Email');
        }

        $row = $table->newRow('phone-number')->setLabel('Telephone')->setValue($contact->getPrimaryPhoneNumber());
        if ($permissions->canChangeSitePhone()) {
            $row->addActionLink('Change', VtsRoutes::of($this->urlHelper)->vtsEditProperty($site->getId(), UpdateVtsPropertyAction::VTS_PHONE_PROPERTY), 'Change Telephone');
        }

        return $table;
    }
}
