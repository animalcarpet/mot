<?php

namespace Event\ViewModel\Event;

use DvsaCommon\Dto\Event\EventDto;
use DvsaCommon\Dto\Event\EventFormDto;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaClient\Entity\Person;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\UrlBuilder\EventUrlBuilderWeb;
use DvsaCommon\Utility\ArrayUtils;

/**
 * Class EventDetailViewModel
 * @package Event\ViewModel\Event
 */
class EventDetailViewModel
{
    /** @var OrganisationDto */
    private $organisation;
    /** @var VehicleTestingStationDto */
    private $site;
    /** @var Person */
    private $person;
    /* @var string $eventType */
    private $eventType;
    /** @var EventDto event prepared for display */
    private $event;
    /** @var EventFormDto */
    private $formModel;

    /**
     * @param OrganisationDto           $organisation
     * @param VehicleTestingStationDto  $site
     * @param Person                    $person
     * @param string                    $eventType
     * @param EventDto                  $event
     */
    public function __construct(
        $organisation,
        $site,
        $person,
        $event,
        $eventType,
        $formModel
    ) {
        $this->setOrganisation($organisation);
        $this->setSite($site);
        $this->setPerson($person);
        $this->setEvent($event);
        $this->setEventType($eventType);
        $this->setEventType($eventType);
        $this->setFormModel($formModel);
    }

    /**
     * This function return the good value for the go back link of the Event list
     *
     * @return string
     */
    public function getGoBackLink()
    {
        switch ($this->eventType) {
            case 'ae':
                return EventUrlBuilderWeb::of()->eventList(
                    $this->organisation->getId(),
                    $this->getEventType()
                )->toString() . '?' . http_build_query($this->formModel->toArray());
            case 'site':
                return EventUrlBuilderWeb::of()
                    ->eventList($this->site->getId(), $this->getEventType())
                    ->toString() . '?' . http_build_query($this->formModel->toArray());
            case 'person':
                return EventUrlBuilderWeb::of()->eventList(
                    $this->person->getId(), $this->getEventType()
                )->toString() . '?' . http_build_query($this->formModel->toArray());
        }
        return '';
    }

    /**
     * @param OrganisationDto $organisation
     * @return $this
     */
    public function setOrganisation($organisation)
    {
        $this->organisation = $organisation;
        return $this;
    }

    /**
     * @return OrganisationDto
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * @return VehicleTestingStationDto
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @param VehicleTestingStationDto $site
     * @return $this
     */
    public function setSite($site)
    {
        $this->site = $site;
        return $this;
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param Person $person
     * @return $this
     */
    public function setPerson($person)
    {
        $this->person = $person;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEventType()
    {
        return $this->eventType;
    }

    /**
     * @param mixed $eventType
     * @return $this
     */
    public function setEventType($eventType)
    {
        $this->eventType = $eventType;
        return $this;
    }

    /**
     * @return EventDto
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param EventDto $event
     * @return $this
     */
    public function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return EventFormDto
     */
    public function getFormModel()
    {
        return $this->formModel;
    }

    /**
     * @param EventFormDto $formModel
     * @return $this
     */
    public function setFormModel($formModel)
    {
        $this->formModel = $formModel;
        return $this;
    }

    public function getTitle()
    {
        switch ($this->eventType) {
            case 'ae':
                return 'Full Details of AE Event selected for';
            case 'site':
                return 'Full Details of Site Event selected for';
            case 'person':
                return 'Full Details of Person Event selected for';
        }
        return '';
    }

    public function getName()
    {
        switch ($this->eventType) {
            case 'ae':
                return sprintf(
                    '%s - %s',
                    $this->organisation->getAuthorisedExaminerAuthorisation()->getAuthorisedExaminerRef(),
                    $this->organisation->getName()
                );
            case 'site':
                return sprintf(
                    '%s - %s',
                    $this->site->getSiteNumber(),
                    $this->site->getName()
                );
            case 'person':
                return sprintf(
                    '%s - %s',
                    $this->person->getUsername(),
                    $this->person->getFullName()
                );
        }
        return '';
    }
}
