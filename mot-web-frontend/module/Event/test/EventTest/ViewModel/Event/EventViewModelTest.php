<?php

namespace EventTest\ViewModel;

use DvsaClient\Entity\Person;
use DvsaCommon\Dto\Event\EventDto;
use DvsaCommon\Dto\Event\EventFormDto;
use DvsaCommon\Dto\Event\EventListDto;
use DvsaCommon\Dto\Organisation\AuthorisedExaminerAuthorisationDto;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\UrlBuilder\AuthorisedExaminerUrlBuilderWeb;
use DvsaCommon\UrlBuilder\EventUrlBuilderWeb;
use DvsaCommon\UrlBuilder\SiteUrlBuilderWeb;
use Event\ViewModel\Event\EventViewModel;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

/**
 * Class EventViewModelTest
 */
class EventViewModelTest extends \PHPUnit_Framework_TestCase
{
    const INVALID_ID = 9999;

    const EVENT_ID = 1;
    const AE_ID = 9;
    const AE_NUMBER = 'A1234';
    const AE_NAME = 'Organisation name';

    const SITE_ID = 1;
    const SITE_NUMBER = 'V1234';
    const SITE_NAME = 'Site name';

    const PERSON_ID = 5;
    const PERSON_USERNAME = 'tester1';
    const PERSON_FIRSTNAME = 'Firstname';
    const PERSON_MIDDLENAME = 'Middlename';
    const PERSON_FAMILYNAME = 'Familyname';

    /**
     * @var EventViewModel
     */
    private $viewModel;

    public function testCreateNewEventViewModel()
    {
        $this->viewModel = new EventViewModel(
            new OrganisationDto(),
            [],
            [],
            $this->getEventFormModel(),
            'organisation',
            self::AE_ID,
            false,
            false
        );
        $this->assertInstanceOf(EventViewModel::class, $this->viewModel);
        $this->assertEquals('organisation', $this->viewModel->getEventType());

        $this->viewModel = new EventViewModel(new OrganisationDto(), [], [], $this->getEventFormModel(), 'site', self::SITE_ID, false);
        $this->assertInstanceOf(EventViewModel::class, $this->viewModel);
        $this->assertEquals('site', $this->viewModel->getEventType());

        $this->viewModel = new EventViewModel(new OrganisationDto(), [], [], $this->getEventFormModel(), 'person', self::PERSON_ID, false);
        $this->assertInstanceOf(EventViewModel::class, $this->viewModel);
        $this->assertEquals('person', $this->viewModel->getEventType());
    }

    public function testEventViewModelGetGoBackLink()
    {
        $organisationDto = new OrganisationDto();
        $organisationDto->setId(self::AE_ID);
        $this->viewModel = new EventViewModel($organisationDto, [], [], $this->getEventFormModel(), 'ae', self::AE_ID, false);
        $this->assertInstanceOf(AuthorisedExaminerUrlBuilderWeb::class, $this->viewModel->getGoBackLink());
        $this->assertSame('/authorised-examiner/' . self::AE_ID, $this->viewModel->getGoBackLink()->toString());

        $site = (new VehicleTestingStationDto())
            ->setId(self::SITE_ID);
        $this->viewModel = new EventViewModel(
            new OrganisationDto(),
            $site,
            [],
            $this->getEventFormModel(),
            'site',
            self::SITE_ID,
            false
        );
        $this->assertInstanceOf(SiteUrlBuilderWeb::class, $this->viewModel->getGoBackLink());
        $this->assertSame('/vehicle-testing-station/' . self::SITE_ID, $this->viewModel->getGoBackLink()->toString());

        $person = (new Person())
            ->setId(1);
        $this->viewModel = new EventViewModel(new OrganisationDto(), [], $person, $this->getEventFormModel(), 'person', self::PERSON_ID, null);
        $this->assertSame('/your-profile', $this->viewModel->getGoBackLink());
    }

    public function testEventViewModelGetCurrentPage()
    {
        $organisationDto = new OrganisationDto();
        $organisationDto->setId(self::AE_ID);
        $this->viewModel = new EventViewModel($organisationDto, [], [], $this->getEventFormModel(), 'ae', self::AE_ID, false);
        $this->assertInstanceOf(EventUrlBuilderWeb::class, $this->viewModel->getCurrentPage());
        $this->assertSame('/event/list/ae/' . self::AE_ID, $this->viewModel->getCurrentPage()->toString());

        $site = (new VehicleTestingStationDto())
            ->setId(self::SITE_ID);
        $this->viewModel = new EventViewModel(
            new OrganisationDto(),
            $site,
            [],
            $this->getEventFormModel(),
            'site',
            self::SITE_ID,
            false
        );
        $this->assertInstanceOf(EventUrlBuilderWeb::class, $this->viewModel->getCurrentPage());
        $this->assertSame('/event/list/site/' . self::SITE_ID, $this->viewModel->getCurrentPage()->toString());

        $person = new Person();
        $person->setId(self::PERSON_ID);
        $this->viewModel = new EventViewModel(new OrganisationDto(), [], $person, $this->getEventFormModel(), 'person', self::PERSON_ID, false);
        $this->assertInstanceOf(EventUrlBuilderWeb::class, $this->viewModel->getCurrentPage());
        $this->assertSame('/event/list/person/' . self::PERSON_ID, $this->viewModel->getCurrentPage()->toString());

        $this->viewModel = new EventViewModel(new OrganisationDto(), [], [], $this->getEventFormModel(), 'invalidType', self::INVALID_ID, false);
        $this->assertEmpty($this->viewModel->getCurrentPage());
    }

    public function testEventViewModelGetTitle()
    {
        $organisationDto = new OrganisationDto();
        $ae = new AuthorisedExaminerAuthorisationDto();
        $ae->setAuthorisedExaminerRef(self::AE_NUMBER);
        $organisationDto->setAuthorisedExaminerAuthorisation($ae);
        $organisationDto->setName(self::AE_NAME);
        $this->viewModel = new EventViewModel($organisationDto, [], [], $this->getEventFormModel(), 'ae', self::AE_ID, false);
        $this->assertSame(
            self::AE_NUMBER . ' - ' . self::AE_NAME,
            $this->viewModel->getTitle()
        );

        $site = (new VehicleTestingStationDto())
            ->setId(self::SITE_ID)
            ->setSiteNumber(self::SITE_NUMBER)
            ->setName(self::SITE_NAME);
        $this->viewModel = new EventViewModel(
            new OrganisationDto(),
            $site,
            [],
            $this->getEventFormModel(),
            'site',
            self::SITE_ID,
            false
        );
        $this->assertSame(
            self::SITE_NUMBER . ' - ' . self::SITE_NAME,
            $this->viewModel->getTitle()
        );

        $person = new Person();
        $person->setUsername(self::PERSON_USERNAME);
        $person->setFirstName(self::PERSON_FIRSTNAME);
        $person->setMiddleName(self::PERSON_MIDDLENAME);
        $person->setFamilyName(self::PERSON_FAMILYNAME);
        $this->viewModel = new EventViewModel(new OrganisationDto(), [], $person, $this->getEventFormModel(), 'person', self::PERSON_ID, false);
        $this->assertSame(
            self::PERSON_FIRSTNAME . ' ' .
            self::PERSON_MIDDLENAME . ' ' .
            self::PERSON_FAMILYNAME,
            $this->viewModel->getTitle()
        );

        $this->viewModel = new EventViewModel(new OrganisationDto(), [], [], $this->getEventFormModel(), 'invalidType', self::INVALID_ID, false);
        $this->assertNull($this->viewModel->getTitle());
    }

    public function testEventViewModelSetGetOrganisation()
    {
        $this->viewModel = new EventViewModel(null, [], [], $this->getEventFormModel(), 'ae', self::AE_ID, false);
        $this->assertInstanceOf(EventViewModel::class, $this->viewModel->setOrganisation(new OrganisationDto()));
        $this->assertInstanceOf(OrganisationDto::class, $this->viewModel->getOrganisation());
    }

    public function testEventViewModelSetGetSite()
    {
        $result = new VehicleTestingStationDto();
        $this->viewModel = new EventViewModel(null, [], [], $this->getEventFormModel(), 'site', self::SITE_ID, false);
        $this->assertInstanceOf(EventViewModel::class, $this->viewModel->setSite($result));
        $this->assertSame($result, $this->viewModel->getSite());
    }

    public function testEventViewModelSetGetPerson()
    {
        $this->viewModel = new EventViewModel(null, [], [], $this->getEventFormModel(), 'person', self::PERSON_ID, false);
        $this->assertInstanceOf(EventViewModel::class, $this->viewModel->setPerson(new Person()));
        $this->assertInstanceOf(Person::class, $this->viewModel->getPerson());
    }

    public function testEventViewModelSetGetFormModel()
    {
        $this->viewModel = new EventViewModel(null, [], [], $this->getEventFormModel(), 'person', self::PERSON_ID, false);
        $this->assertInstanceOf(EventViewModel::class, $this->viewModel->setFormModel($this->getEventFormModel()));
        $this->assertInstanceOf(EventFormDto::class, $this->viewModel->getFormModel());
    }

    public function testEventViewModelSetGetEventType()
    {
        $this->viewModel = new EventViewModel(null, [], [], $this->getEventFormModel(), 'person', self::PERSON_ID, false);
        $this->assertInstanceOf(EventViewModel::class, $this->viewModel->setEventType('event'));
        $this->assertSame('event', $this->viewModel->getEventType());
    }

    public function testEventViewModelSetGetEventList()
    {
        $this->viewModel = new EventViewModel(null, [], [], $this->getEventFormModel(), 'person', self::PERSON_ID, false);
        $this->assertInstanceOf(EventViewModel::class, $this->viewModel->setEventList(['events' => []]));
        $this->assertArrayHasKey('events', $this->viewModel->getEventList());
    }

    private function getEventFormModel()
    {
        return new EventFormDto([]);
    }

    public function testGetEventDetailLink()
    {
        $this->viewModel = new EventViewModel(null, [], [], $this->getEventFormModel(), 'ae', self::AE_ID, false);
        $this->assertEquals('/event/ae/' . self::AE_ID . '/' . self::EVENT_ID . '?goBack=', $this->viewModel->getEventDetailLink(self::EVENT_ID));
    }

    public function testGetViewOrJson()
    {
        $this->viewModel = new EventViewModel(null, [], [], $this->getEventFormModel(), 'ae', self::AE_ID, false);
        $events = new EventListDto();
        $events->setEvents([]);
        $this->viewModel->setEventList($events);
        $this->assertInstanceOf(ViewModel::class, $this->viewModel->getViewOrJson(false));
        $this->assertInstanceOf(JsonModel::class, $this->viewModel->getViewOrJson(true));
    }

    public function testParseEventForJson()
    {
        $this->viewModel = new EventViewModel(null, [], [], $this->getEventFormModel(), 'ae', self::AE_ID, false);
        $events = new EventListDto();
        $event = new EventDto();
        $event->setId(self::EVENT_ID);
        $events->setEvents([$event]);
        $this->viewModel->setEventList($events);

        $result = $this->viewModel->parseEventForJson();
        $this->assertArrayHasKey('type', $result[0]);
        $this->assertArrayHasKey('date', $result[0]);
        $this->assertArrayHasKey('description', $result[0]);
    }
}
