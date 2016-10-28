<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use PHPUnit_Framework_Assert as PHPUnit;
use Dvsa\Mot\Behat\Support\Api\Event;
use Dvsa\Mot\Behat\Support\Api\Person;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use Dvsa\Mot\Behat\Support\Data\Params\EventParams;
use Dvsa\Mot\Behat\Support\Data\Params\PersonParams;
use Dvsa\Mot\Behat\Support\Data\Params\SiteParams;
use Dvsa\Mot\Behat\Support\Data\Params\AuthorisedExaminerParams;

class ReasonForRejection implements Context
{
    /**
     * @var SessionContext
     */
    private $sessionContext;


    /**
     * @param Event $event
     */
    public function __construct(Event $event, Person $person, Session $session, TestSupportHelper $testSupportHelper)
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
     * @Then a status change event is generated for the user of :eventType
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

    private function getPersonDisplayName()
    {
        $response = $this->person->getPersonDetails($this->sessionContext->getCurrentAccessToken(),$this->sessionContext->getCurrentUserId());
        $data = $response->getBody()->getData();

        return implode(" ", array_filter([$data[PersonParams::FIRST_NAME], $data[PersonParams::MIDDLE_NAME], $data[PersonParams::SURNAME]]));
    }
}
