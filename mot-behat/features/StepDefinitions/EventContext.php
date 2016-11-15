<?php

use Behat\Behat\Context\Context;
use PHPUnit_Framework_Assert as PHPUnit;
use Dvsa\Mot\Behat\Support\Api\Event;
use Dvsa\Mot\Behat\Support\Api\Person;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Data\AuthorisedExaminerData;
use Dvsa\Mot\Behat\Support\Data\SiteData;
use Dvsa\Mot\Behat\Support\Data\UserData;
use DvsaCommon\Utility\DtoHydrator;
use DvsaCommon\Dto\Event\EventListDto;
use Dvsa\Mot\Behat\Support\Data\Params\EventParams;
use Dvsa\Mot\Behat\Support\Data\Params\PersonParams;
use DvsaCommon\Dto\Event\EventDto;
use DvsaCommon\Enum\EventCategoryCode;
use Zend\Http\Response as HttpResponse;

class EventContext implements Context
{
    private $event;

    private $person;

    private $eventCreationData = [];

    private $eventCreationEntityId;

    private $authorisedExaminerData;

    private $siteData;

    private $userData;

    /**
     * @var EventDto
     */
    private $userEvent;


    public function __construct(
        Event $event,
        Person $person,
        AuthorisedExaminerData $authorisedExaminerData,
        SiteData $siteData,
        UserData $userData
    )
    {
        $this->event = $event;
        $this->person = $person;
        $this->authorisedExaminerData = $authorisedExaminerData;
        $this->siteData = $siteData;
        $this->userData = $userData;
    }

    /**
     * @Given I create an event for :username
     */
    public function iCreateAnEventForAPerson($username)
    {
        $user = $this->userData->createUser($username);
        $this->eventCreationEntityId = $user->getUserId();
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
     * @Then a status change event is generated for :user of :type
     * @Then an event is generated for the user of :type
     * @Then an event is generated for :user of :type
     */
    public function aStatusChangeEventIsGeneratedForTheUserOf(AuthenticatedUser $user, $type)
    {
        $response = $this->event->getPersonEventsData($this->userData->getCurrentLoggedUser()->getAccessToken(), $user->getUserId());
        $data = $response->getBody()->getData();

        /** @var EventListDto $eventList */
        $eventList = DtoHydrator::jsonToDto($data);
        $events = $eventList->getEvents();

        PHPUnit::assertNotEmpty($eventList);

        $found = false;
        foreach ($events as $event) {
            if ($event->getType() === $type) {
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
        PHPUnit::assertContains($phrase, $this->userEvent->getDescription());
    }

    /**
     * @Then an event description contains my name
     */
    public function anEventDescriptionContainsMyName()
    {
        $this->anEventDescriptionContainsPhrase($this->getPersonDisplayName());
    }

    /**
     * @Then a status change event is NOT generated for the :user of :type
     */
    public function aStatusChangeEventIsNotGeneratedForTheUserOf(AuthenticatedUser $user, $type)
    {
        $response = $this->event->getPersonEventsData(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $user->getUserId()
        );
        $data = $response->getBody()->getData();

        /** @var EventListDto $eventList */
        $eventList = DtoHydrator::jsonToDto($data);
        $events = $eventList->getEvents();

        $found = false;
        foreach ($events as $event) {
            if ($event->getType() === $type) {
                $found = true;
                break;
            }
        }

        PHPUnit::assertFalse($found);
    }

    /**
     * @Then a site event is generated for the site of :type
     */
    public function aSiteEventIsGeneratedForTheSiteOf($type)
    {
        $areaOffice1User = $this->userData->createAreaOffice1User();
        $siteId = $this->siteData->get()->getId();

        $response = $this->event->getSiteEventsData($areaOffice1User->getAccessToken(), $siteId);
        $data = $response->getBody()->getData();

        /** @var EventListDto $eventList */
        $eventList = DtoHydrator::jsonToDto($data);
        $events = $eventList->getEvents();

        $found = false;
        foreach ($events as $event) {
            if ($event->getType() === $type) {
                $found = true;
                break;
            }
        }

        PHPUnit::assertTrue($found);
    }

    /**
     * @Then an organisation event is generated for the organisation of :type
     */
    public function anOrganisationEventIsGeneratedForTheOrganisationOf($type)
    {
        $areaOffice1User = $this->userData->createAreaOffice1User();
        $aeId = $this->authorisedExaminerData->get()->getId();

        $response = $this->event->getOrganisationEventsData($areaOffice1User->getAccessToken(), $aeId);
        $data = $response->getBody()->getData();

        /** @var EventListDto $eventList */
        $eventList = DtoHydrator::jsonToDto($data);
        $events = $eventList->getEvents();

        $found = false;
        foreach ($events as $event) {
            if ($event->getType() === $type) {
                $found = true;
                break;
            }
        }

        PHPUnit::assertTrue($found, "Event {$type} not found");
    }

    private function getPersonDisplayName()
    {
        $user = $this->userData->getCurrentLoggedUser();
        $response = $this->person->getPersonDetails($user->getAccessToken(), $user->getUserId());
        $data = $response->getBody()->getData();

        return implode(" ", array_filter([$data[PersonParams::FIRST_NAME], $data[PersonParams::MIDDLE_NAME], $data[PersonParams::SURNAME]]));
    }
}
