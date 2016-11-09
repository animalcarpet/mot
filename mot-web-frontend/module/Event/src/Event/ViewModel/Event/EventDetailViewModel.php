<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Event\ViewModel\Event;

use DvsaClient\Entity\Person;
use DvsaCommon\Dto\Event\EventDto;
use DvsaCommon\Dto\Event\EventFormDto;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\UrlBuilder\EventUrlBuilderWeb;
use Event\Controller\EventController;

/**
 * Class EventDetailViewModel.
 */
class EventDetailViewModel
{
    /**
     * @var OrganisationDto */
    private $organisation;

    /**
     * @var VehicleTestingStationDto */
    private $site;

    /**
     * @var Person */
    private $person;

    /**
     * @var string $eventType */
    private $eventType;

    /**
     * @var EventDto event prepared for display */
    private $event;

    /**
     * @var EventFormDto */
    private $formModel;

    /**
     * @var string */
    private $previousRoute;

    /**
     * @param OrganisationDto          $organisation
     * @param VehicleTestingStationDto $site
     * @param Person                   $person
     * @param string                   $eventType
     * @param EventDto                 $event
     * @param EventFormDto             $formModel
     * @param string                   $previousRoute
     */
    public function __construct(
        $organisation,
        $site,
        $person,
        $event,
        $eventType,
        $formModel,
        $previousRoute
    ) {
        $this->previousRoute = $previousRoute;

        $this->setOrganisation($organisation);
        $this->setSite($site);
        $this->setPerson($person);
        $this->setEvent($event);
        $this->setEventType($eventType);
        $this->setEventType($eventType);
        $this->setFormModel($formModel);
    }

    /**
     * This function return the good value for the go back link of the Event list.
     *
     * @return string
     */
    public function getGoBackLink()
    {
        $url = '';

        switch ($this->eventType) {
            case 'ae':
                $url = EventUrlBuilderWeb::of()->eventList(
                    $this->organisation->getId(),
                    $this->getEventType()
                )->toString() . '?' . http_build_query($this->formModel->toArray());
                break;
            case 'site':
                $url = EventUrlBuilderWeb::of()
                    ->eventList($this->site->getId(), $this->getEventType())
                    ->toString() . '?' . http_build_query($this->formModel->toArray());
                break;
            case 'person':
                $url = EventUrlBuilderWeb::of()->eventList(
                    $this->person->getId(), $this->getEventType()
                )->toString() . '?' . http_build_query($this->formModel->toArray());
                break;
        }

        if (null !== $this->previousRoute) {
            $url = sprintf('%s&%s=%s', $url,
                EventController::PERSON_PROFILE_GO_BACK_PARAMETER, urlencode($this->previousRoute));
        }

        return $url;
    }

    /**
     * @param OrganisationDto $organisation
     *
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
     *
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
     *
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
     *
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
     *
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
     *
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
                return 'AE Event for';
            case 'site':
                return 'Site Event for';
            case 'person':
                return 'Person Event for';
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
