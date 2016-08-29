<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use PHPUnit_Framework_Assert as PHPUnit;
use Dvsa\Mot\Behat\Support\Api\Event;
use Dvsa\Mot\Behat\Support\Api\Person;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;

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

    /**
     * @param Event $event
     */
    public function __construct(
        Event $event,
        Person $person,
        Session $session,
        TestSupportHelper $testSupportHelper
    )
    {
        $this->event = $event;
        $this->person = $person;
        $this->session = $session;
        $this->testSupportHelper = $testSupportHelper;
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
        $this->eventCreationData['eventCategoryCode'] = 'NT';
    }

    /**
     * @Given I create an event for an organisation
     */
    public function iCreateAnEventForAnOrganisation()
    {
        $this->aeContext->createAE();
        $ae = $this->aeContext->getAE();
        $this->eventCreationEntityId = $ae['id'];
        $this->eventCreationData['eventCategoryCode'] = 'AE';
    }

    /**
     * @Given I create an event for a site
     */
    public function iCreateAnEventForASite()
    {
        $this->vtsContext->createSite();
        $site = $this->vtsContext->getSite();
        $this->eventCreationEntityId = $site['id'];

        $this->eventCreationData['eventCategoryCode'] = 'VTS';

    }

    /**
     * @Given I select the event type :eventTypeCode
     */
    public function iSelectTheEventType($eventTypeCode)
    {
        $this->eventCreationData['eventTypeCode'] = $eventTypeCode;
    }

    /**
     * @Given I supply a valid date
     */
    public function iSupplyAValidDate()
    {
        $dt = new \DateTime();
        $this->eventCreationData['eventDate'] = [
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
        $this->eventCreationData['eventOutcomeCode'] = $eventOutcomeCode;
    }

    /**
     * @Given I supply a blank description
     */
    public function iSupplyABlankDescription()
    {
        $this->eventCreationData['eventDescription'] = '';
    }

    /**
     * @When I submit the event
     */
    public function iSubmitTheEvent()
    {
        $reponse = $this->event->postEvent(
            $this->sessionContext->getCurrentAccessToken(),
            $this->eventCreationData['eventCategoryCode'],
            $this->eventCreationEntityId,
            $this->eventCreationData
        );
        PHPUnit::assertSame(200, $reponse->getStatusCode());
    }

    /**
     * @When I submit the non manual event
     */
    public function iSubmitTheNonManualEvent()
    {
        $this->eventCreationData['description'] = 'Card order';
        $reponse = $this->event->postNonManualEvent(
            $this->sessionContext->getCurrentAccessToken(),
            $this->personContext->getPersonUserId(),
            $this->eventCreationData
        );
        PHPUnit::assertSame(200, $reponse->getStatusCode());
    }

    /**
     * @Then a status change event is generated for the user of :eventType
     * @Then an event is generated for the user of :eventType
     */
    public function aStatusChangeEventIsGeneratedForTheUserOf($eventType)
    {
        $response = $this->event->getPersonEventsData($this->sessionContext->getCurrentAccessToken(), $this->personContext->getPersonUserId());
        $data = $response->getBody()->toArray()["data"];
        $eventList = $data["events"];

        PHPUnit::assertNotEmpty($eventList);

        $found = false;
        foreach ($eventList as $event) {
            if ($event["type"] === $eventType) {
                $this->userEvent = $event;
                $found = true;
                break;
            }
        }

        PHPUnit::assertTrue($found, "Event type {$eventType} not found");
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
        $data = $response->getBody()->toArray()["data"];
        $eventList = $data["events"];

        $found = false;
        foreach ($eventList as $event) {
            if ($event["type"] === $eventType) {
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
        $areaOffice1Service = $this->testSupportHelper->getAreaOffice1Service();
        $ao = $areaOffice1Service->create([]);
        $aoSession = $this->session->startSession(
            $ao->data["username"],
            $ao->data["password"]
        );

        $site = $this->vtsContext->getSite();

        $response = $this->event->getSiteEventsData($aoSession->getAccessToken(), $site["id"]);
        $data = $response->getBody()->toArray()["data"];
        $eventList = $data["events"];

        $found = false;
        foreach ($eventList as $event) {
            if ($event["type"] === $eventType) {
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
        $areaOffice1Service = $this->testSupportHelper->getAreaOffice1Service();
        $ao = $areaOffice1Service->create([]);
        $aoSession = $this->session->startSession(
            $ao->data["username"],
            $ao->data["password"]
        );

        $ae = $this->aeContext->getAE();

        $response = $this->event->getOrganisationEventsData($aoSession->getAccessToken(), $ae["id"]);
        $data = $response->getBody()->toArray()["data"];
        $eventList = $data["events"];

        $found = false;
        foreach ($eventList as $event) {
            if ($event["type"] === $eventType) {
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
        $data = $response->getBody()->toArray()["data"];

        return implode(" ", array_filter([$data["firstName"], $data["middleName"], $data["surname"]]));
    }
}
