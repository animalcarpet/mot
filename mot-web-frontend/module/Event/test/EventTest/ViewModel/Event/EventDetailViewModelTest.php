<?php

namespace EventTest\ViewModel;

use DvsaClient\Entity\Person;
use DvsaCommon\Dto\Event\EventDto;
use DvsaCommon\Dto\Event\EventFormDto;
use DvsaCommon\Dto\Organisation\AuthorisedExaminerAuthorisationDto;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use Event\ViewModel\Event\EventDetailViewModel;
use Zend\View\Model\ViewModel;

/**
 * Class EventDetailViewModelTest.
 */
class EventDetailViewModelTest extends \PHPUnit_Framework_TestCase
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
     * @var EventDetailViewModel
     */
    private $viewModel;

    public function testCreateNewEventViewModel()
    {
        $this->viewModel = new EventDetailViewModel(
            new OrganisationDto(),
            [],
            [],
            new EventDto(),
            'organisation',
            new EventFormDto(),
            false,
            ''
        );
        $this->assertInstanceOf(EventDetailViewModel::class, $this->viewModel);
        $this->assertEquals('organisation', $this->viewModel->getEventType());

        $this->viewModel = new EventDetailViewModel(new OrganisationDto(), [], [], new EventDto(), 'site', new EventFormDto(), false, '');
        $this->assertInstanceOf(EventDetailViewModel::class, $this->viewModel);
        $this->assertEquals('site', $this->viewModel->getEventType());

        $this->viewModel = new EventDetailViewModel(new OrganisationDto(), [], [], new EventDto(), 'person', new EventFormDto(), false, '');
        $this->assertInstanceOf(EventDetailViewModel::class, $this->viewModel);
        $this->assertEquals('person', $this->viewModel->getEventType());
    }

    public function testGetterSetterOrganisation()
    {
        $this->viewModel = new EventDetailViewModel(null, null, null, null, null, null, null, null);
        $this->assertInstanceOf(EventDetailViewModel::class, $this->viewModel->setOrganisation(new OrganisationDto()));
        $this->assertInstanceOf(OrganisationDto::class, $this->viewModel->getOrganisation());
    }

    public function testGetterSetterSite()
    {
        $result = new VehicleTestingStationDto();
        $this->viewModel = new EventDetailViewModel(null, null, null, null, null, null, null, null);
        $this->assertInstanceOf(EventDetailViewModel::class, $this->viewModel->setSite($result));
        $this->assertEquals($result, $this->viewModel->getSite());
    }

    public function testGetterSetterPerson()
    {
        $this->viewModel = new EventDetailViewModel(null, null, null, null, null, null, null, null);
        $this->assertInstanceOf(EventDetailViewModel::class, $this->viewModel->setPerson(new Person()));
        $this->assertInstanceOf(Person::class, $this->viewModel->getPerson());
    }

    public function testGetterSetterEvent()
    {
        $this->viewModel = new EventDetailViewModel(null, null, null, null, null, null, null, null);
        $this->assertInstanceOf(EventDetailViewModel::class, $this->viewModel->setEvent(new EventDto()));
        $this->assertInstanceOf(EventDto::class, $this->viewModel->getEvent());
    }

    public function testGetterSetterFormModel()
    {
        $this->viewModel = new EventDetailViewModel(null, null, null, null, null, null, null, null);
        $this->assertInstanceOf(EventDetailViewModel::class, $this->viewModel->setFormModel(new EventFormDto()));
        $this->assertInstanceOf(EventFormDto::class, $this->viewModel->getFormModel());
    }

    public function testEventViewModelGetGoBackLink()
    {
        $organisation = new OrganisationDto();
        $organisation->setId(self::AE_ID);
        $data = [
            'search' => 'search',
            'isShowDate' => true,
            'dateFrom' => [
                'Day' => 01,
                'Month' => 01,
                'Year' => 2015,
            ],
            'dateTo' => [
                'Day' => 01,
                'Month' => 01,
                'Year' => 2015,
            ],
        ];
        $site = (new VehicleTestingStationDto())
            ->setId(self::SITE_ID);

        $this->viewModel = new EventDetailViewModel($organisation, $site, null, null, 'ae', new EventFormDto($data), false, '');
        $this->assertEquals('/event/list/ae/9?search=search&isShowDate=1&dateFrom%5BDay%5D=1&dateFrom%5BMonth%5D=1&dateFrom%5BYear%5D=2015&dateTo%5BDay%5D=1&dateTo%5BMonth%5D=1&dateTo%5BYear%5D=2015&goBack=', $this->viewModel->getGoBackLink());
        $this->viewModel->setEventType('site');
        $this->assertEquals('/event/list/site/1?search=search&isShowDate=1&dateFrom%5BDay%5D=1&dateFrom%5BMonth%5D=1&dateFrom%5BYear%5D=2015&dateTo%5BDay%5D=1&dateTo%5BMonth%5D=1&dateTo%5BYear%5D=2015&goBack=', $this->viewModel->getGoBackLink());
        $this->viewModel->setEventType('');
        $this->assertEquals('&goBack=', $this->viewModel->getGoBackLink());
    }

    public function testGetTitleName()
    {
        $organisation = new OrganisationDto();
        $ae = new AuthorisedExaminerAuthorisationDto();
        $ae->setAuthorisedExaminerRef(self::AE_NUMBER);
        $organisation
            ->setAuthorisedExaminerAuthorisation($ae)
            ->setName(self::AE_NAME);
        $site = (new VehicleTestingStationDto())
            ->setId(self::SITE_ID)
            ->setSiteNumber(self::SITE_NUMBER)
            ->setName(self::SITE_NAME);
        $person = new Person();
        $person
            ->setUsername(self::PERSON_USERNAME)
            ->setFirstName(self::PERSON_FIRSTNAME)
            ->setMiddleName(self::PERSON_MIDDLENAME)
            ->setFamilyName(self::PERSON_FAMILYNAME);

        $this->viewModel = new EventDetailViewModel($organisation, $site, $person, null, 'ae', new EventFormDto(), false, '');
        $this->assertEquals('AE Event for', $this->viewModel->getTitle());
        $this->assertEquals(self::AE_NUMBER.' - '.self::AE_NAME, $this->viewModel->getName());
        $this->viewModel->setEventType('site');
        $this->assertEquals('Site Event for', $this->viewModel->getTitle());
        $this->assertEquals(self::SITE_NUMBER.' - '.self::SITE_NAME, $this->viewModel->getName());
        $this->viewModel->setEventType('person');
        $this->assertEquals('Person Event for', $this->viewModel->getTitle());
        $this->assertSame(
            self::PERSON_USERNAME.' - '.
            self::PERSON_FIRSTNAME.' '.
            self::PERSON_MIDDLENAME.' '.
            self::PERSON_FAMILYNAME,
            $this->viewModel->getName()
        );
        $this->viewModel->setEventType('invalid');
        $this->assertEquals('', $this->viewModel->getTitle());
        $this->assertEquals('', $this->viewModel->getName());
    }
}
