<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use PHPUnit_Framework_Assert as PHPUnit;
use Dvsa\Mot\Behat\Support\Api\Event;
use Dvsa\Mot\Behat\Support\Api\Person;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use Dvsa\Mot\Behat\Support\Data\AuthorisedExaminerData;
use Dvsa\Mot\Behat\Support\Data\SiteData;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Dvsa\Mot\Behat\Support\Data\Params\AuthorisedExaminerParams;
use Dvsa\Mot\Behat\Support\Data\Params\EventParams;
use Dvsa\Mot\Behat\Support\Data\Params\PersonParams;
use Dvsa\Mot\Behat\Support\Data\Params\SiteParams;
use DvsaCommon\Enum\EventCategoryCode;
use Zend\Http\Response as HttpResponse;

class EventContext implements Context
{
    /**
     * @var SessionContext
     */
    private $sessionContext;

    /**
     * @var PersonContext
     */
    private $personContext;

    /**
     * @var VtsContext
     */
    private $vtsContext;

    /**
     * @var AuthorisedExaminerContext
     */
    private $aeContext;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var Person
     */
    private $person;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var TestSupportHelper
     */
    private $testSupportHelper;

    /** @var array */
    private $siteEvent = [];

    /**
     * Used to store data in preparation for manually creating an event
     * @var array
     */
    private $eventCreationData;

    /**
     * @var int
     */
    private $eventCreationEntityId;

    private $authorisedExaminerData;

    private $siteData;

    private $userData;

    /**
     * @param Event $event
     */
    public function __construct(
        Event $event,
        Person $person,
        Session $session,
        TestSupportHelper $testSupportHelper,
        AuthorisedExaminerData $authorisedExaminerData,
        SiteData $siteData,
        UserData $userData
    )
    {
        $this->event = $event;
        $this->person = $person;
        $this->session = $session;
        $this->testSupportHelper = $testSupportHelper;
        $this->authorisedExaminerData = $authorisedExaminerData;
        $this->siteData = $siteData;
        $this->userData = $userData;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->personContext = $scope->getEnvironment()->getContext(PersonContext::class);
        $this->sessionContext = $scope->getEnvironment()->getContext(SessionContext::class);
        $this->vtsContext = $scope->getEnvironment()->getContext(VtsContext::class);
        $this->aeContext = $scope->getEnvironment()->getContext(AuthorisedExaminerContext::class);
    }

    /**
     * @Given I create an event for a person
     */
    public function iCreateAnEventForAPerson()
    {
        // We also need to create a person here
        $this->personContext->theUserExists('User');
        $this->eventCreationEntityId = $this->personContext->getPersonUserId();
        $this->eventCreationData[EventParams::EVENT_CATEGORY_CODE] = EventCategoryCode::NT_EVENTS;
    }

    /**
     * @Given I create an event for an organisation
     */
    public function iCreateAnEventForAnOrganisation()
    {
        $ae = $this->authorisedExaminerData->get();
        $this->eventCreationEntityId = $ae->getId();
        $this->eventCreationData[EventParams::EVENT_CATEGORY_CODE] = EventCategoryCode::AE_EVENT;
    }

    /**
     * @Given I create an event for a site
     */
    public function iCreateAnEventForASite()
    {
        $site = $this->siteData->get();
        $this->eventCreationEntityId = $site->getId();

        $this->eventCreationData[EventParams::EVENT_CATEGORY_CODE] = EventCategoryCode::VTS_EVENT;

    }

    /**
     * @Given I select the event type :eventTypeCode
     */
    public function iSelectTheEventType($eventTypeCode)
    {
        $this->eventCreationData[EventParams::EVENT_TYPE_CODE] = $eventTypeCode;
    }

    /**
     * @Given I supply a valid date
     */
    public function iSupplyAValidDate()
    {
        $dt = new \DateTime();
        $this->eventCreationData[EventParams::EVENT_DATE] = [
            'day' => $dt->format('d'),
            'month' => $dt->format('m'),
            'year' => $dt->format('Y')
        ];
    }

    /**
     * @Given I select the event outcome :eventOutcomeCode
     */
    public function iSelectTheEventOutcome($eventOutcomeCode)
    {
        $this->eventCreationData[EventParams::EVENT_OUTCOME_CODE] = $eventOutcomeCode;
    }

    /**
     * @Given I supply a blank description
     */
    public function iSupplyABlankDescription()
    {
        $this->eventCreationData[EventParams::EVENT_DESCRIPTION] = '';
    }

    /**
     * @When I submit the event
     */
    public function iSubmitTheEvent()
    {
        $reponse = $this->event->postEvent(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $this->eventCreationData[EventParams::EVENT_CATEGORY_CODE],
            $this->eventCreationEntityId,
            $this->eventCreationData
        );
        PHPUnit::assertSame(HttpResponse::STATUS_CODE_200, $reponse->getStatusCode());
    }

    /**
     * @When I submit :eventType non manual event for :user
     */
    public function iSubmitTheNonManualEvent($eventType, AuthenticatedUser $user)
    {
        $data = [
            EventParams::EVENT_TYPE_CODE => $eventType,
            EventParams::DESCRIPTION => 'Card order',
            EventParams::EVENT_CATEGORY_CODE => EventCategoryCode::NT_EVENTS
        ];

        $reponse = $this->event->postNonManualEvent(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $user->getUserId(),
            $data
        );

        PHPUnit::assertSame(HttpResponse::STATUS_CODE_200, $reponse->getStatusCode());
    }

    /**
     * @Then a status change event is generated for the user of :eventType
     * @Then an event is generated for the user of :eventType
     */
    public function aStatusChangeEventIsGeneratedForTheUserOf($eventType)
    {
        $response = $this->event->getPersonEventsData($this->sessionContext->getCurrentAccessToken(), $this->personContext->getPersonUserId());
        $data = $response->getBody()->getData();
        $eventList = $data["events"];

        PHPUnit::assertNotEmpty($eventList);

        $found = false;
        foreach ($eventList as $event) {
            if ($event[EventParams::TYPE] === $eventType) {
                $this->userEvent = $event;
                $found = true;
                break;
            }
        }

        PHPUnit::assertTrue($found, "Event type {$eventType} not found");
    }

    /**
     * @Then an event is generated for :user of :type
     */
    public function aStatusChangeEventIsGeneratedForTheUserOfEventType(AuthenticatedUser $user, $type)
    {
        $response = $this->event->getPersonEventsData($this->userData->getCurrentLoggedUser()->getAccessToken(), $user->getUserId());
        $data = $response->getBody()->getData();
        $eventList = $data["events"];

        PHPUnit::assertNotEmpty($eventList);

        $found = false;
        foreach ($eventList as $event) {
            if ($event[EventParams::TYPE] === $type) {
                $this->userEvent = $event;
                $found = true;
                break;
            }
        }

        PHPUnit::assertTrue($found, "Event type {$type} not found");
    }

    /**
     * @Then an event description contains phrase :phrase
     */
    public function anEventDescriptionContainsPhrase($phrase)
    {
        PHPUnit::assertContains($phrase, $this->userEvent["description"]);
    }

    /**
     * @Then an event description contains my name
     */
    public function anEventDescriptionContainsMyName()
    {
        $this->anEventDescriptionContainsPhrase($this->getPersonDisplayName());
    }

    /**
     * @Then a status change event is NOT generated for the user of :eventType
     */
    public function aStatusChangeEventIsNotGeneratedForTheUserOf($eventType)
    {
        $response = $this->event->getPersonEventsData($this->sessionContext->getCurrentAccessToken(), $this->personContext->getPersonUserId());
        $data = $response->getBody()->getData();
        $eventList = $data["events"];

        $found = false;
        foreach ($eventList as $event) {
            if ($event[EventParams::TYPE] === $eventType) {
                $found = true;
                break;
            }
        }

        PHPUnit::assertFalse($found);
    }

    /**
     * @Then a site event is generated for the site of :eventType
     */
    public function aSiteEventIsGeneratedForTheSiteOf($eventType)
    {
        $areaOffice1User = $this->userData->createAreaOffice1User();
        $siteId = $this->siteData->get()->getId();

        $response = $this->event->getSiteEventsData($areaOffice1User->getAccessToken(), $siteId);
        $data = $response->getBody()->getData();
        $eventList = $data["events"];

        $found = false;
        foreach ($eventList as $event) {
            if ($event[EventParams::TYPE] === $eventType) {
                $this->siteEvent = $event;
                $found = true;
                break;
            }
        }

        PHPUnit::assertTrue($found);
    }

    /**
     * @Then an organisation event is generated for the organisation of :eventType
     */
    public function anOrganisationEventIsGeneratedForTheOrganisationOf($eventType)
    {
        $areaOffice1User = $this->userData->createAreaOffice1User();
        $aeId = $this->authorisedExaminerData->get()->getId();

        $ae = $this->authorisedExaminerData->get();

        $response = $this->event->getOrganisationEventsData($areaOffice1User->getAccessToken(), $aeId);
        $data = $response->getBody()->getData();
        $eventList = $data["events"];

        $found = false;
        foreach ($eventList as $event) {
            if ($event[EventParams::TYPE] === $eventType) {
                $this->siteEvent = $event;
                $found = true;
                break;
            }
        }

        PHPUnit::assertTrue($found, "Event {$eventType} not found");
    }

    private function getPersonDisplayName()
    {
        $response = $this->person->getPersonDetails($this->sessionContext->getCurrentAccessToken(),$this->sessionContext->getCurrentUserId());
        $data = $response->getBody()->getData();

        return implode(" ", array_filter([$data[PersonParams::FIRST_NAME], $data[PersonParams::MIDDLE_NAME], $data[PersonParams::SURNAME]]));
    }
}
