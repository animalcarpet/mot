<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Event\Controller;

use Core\Controller\AbstractAuthActionController;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Dto\Common\DateDto;
use DvsaCommon\Dto\Event\EventFormDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\UrlBuilder\PersonUrlBuilderWeb;
use DvsaCommon\Utility\DtoHydrator;
use Event\ViewModel\Event\EventDetailViewModel;
use Event\ViewModel\Event\EventViewModel;
use Organisation\Traits\OrganisationServicesTrait;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\View\Model\ViewModel;

/**
 * Event Controller.
 */
class EventController extends AbstractAuthActionController
{
    use OrganisationServicesTrait;

    const PERSON_PROFILE_GO_BACK_PARAMETER = 'goBack';

    const DATE_FROM_BEFORE_TO = 'Date From can\'t be after Date To';
    const DATE_INVALID = 'Date %s: Given date does not exist ("%s")';
    const DATE_RANGE_INVALID = 'Date %s must be between 01 January 1900 and today';
    const DATE_MISSING = 'Date %s: Enter a date in the format dd mm yyyy';
    const DATE_BEFORE = '01-01-1900';
    const DATE_FORMAT = 'd-m-Y';

    const ROUTE_LIST = 'event-list';

    const TYPE_AE = 'ae';

    /**
     * @var ContextProvider
     */
    private $contextProvider;

    /**
     * EventController constructor.
     * 
     * @param ContextProvider $contextProvider
     */
    public function __construct(ContextProvider $contextProvider)
    {
        $this->contextProvider = $contextProvider;
    }

    /**
     * This is the common action who allow us to get the information needed
     * and build the view.
     *
     * @return Response|ViewModel
     */
    public function listAction()
    {
        $this->layout()->setVariable('pageSubTitle', 'Events');
        $this->layout()->setVariable('pageTitle', 'Events history');

        // breadcrumbs removed for now as devs don't have time to look at them
        /*
        $breadcrumbs = [
            'Entity name'    => 'entity_profile_page_URL',
            'Events history' => '',
        ];
        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);
        */

        $this->layout('layout/layout-govuk.phtml');
        $this->setHeadTitle('Events history');

        $id = $this->params()->fromRoute('id');
        $type = $this->params()->fromRoute('type');

        if ($this->getAuthorizationService()->isGranted(PermissionInSystem::LIST_EVENT_HISTORY) === false) {
            return $this->redirect()->toUrl(PersonUrlBuilderWeb::home());
        }

        /* @var Request $request */
        $request = $this->getRequest();

        $formData = $request->getQuery()->toArray();

        // get the previous route from url until /event/list is refactored to new profile
        $previousRoute = urldecode($this->getRequest()->getQuery(self::PERSON_PROFILE_GO_BACK_PARAMETER));

        $viewModel = new EventViewModel(
            $this->getOrganisation($id, $type),
            $this->getSite($id, $type),
            $this->getPerson((int) $id, $type),
            new EventFormDto($formData),
            $type,
            $id,
            $previousRoute
        );

        $events = $this->getEvents($id, $type, $this->validateForm($viewModel->getFormModel(),
            $request->isXmlHttpRequest()));

        $viewModel->setEventList($events);

        if ($request->isXmlHttpRequest()) {
            return $viewModel->getJsonModel();
        }

        // Now inject our type and ids into the actual ViewModel object
        $viewModel = $viewModel->getViewModel();
        $viewModel->setVariable('type', $type);
        $viewModel->setVariable('id', $id);
        $viewModel->setVariable(
            'canRecordEvent',
            $this->getAuthorizationService()->isGranted(PermissionInSystem::EVENT_CREATE)
        );
        $viewModel->setVariable(self::PERSON_PROFILE_GO_BACK_PARAMETER, $previousRoute);

        return $viewModel;
    }

    /**
     * @return Response|ViewModel
     */
    public function createAction()
    {
        $type = $this->params()->fromRoute('type');

        var_dump($this->getCatalogService()->getEventTypesWithOutcomesForCategory($type));
        die;
    }

    /**
     * This function is responsible to validate that the form is valid.
     *
     * @param EventFormDto $formModel
     * @param Boolean      $isAjax
     *
     * @return EventFormDto|null
     */
    protected function validateForm(EventFormDto $formModel, $isAjax)
    {
        if ($formModel->isShowDate() === true && $isAjax === false) {
            return $this->validateDatesAndRange($formModel);
        }

        return $formModel;
    }

    /**
     * This function prepare the two date form to be validated.
     *
     * @param EventFormDto $formModel
     *
     * @return EventFormDto|null
     */
    protected function validateDatesAndRange(EventFormDto $formModel)
    {
        $dateFrom = $formModel->getDateFrom();
        $dateTo = $formModel->getDateTo();

        $this->validateDate($dateFrom, 'From');
        $this->validateDate($dateTo, 'To');

        if (count($this->flashMessenger()->getCurrentErrorMessages()) === 0
            && $dateFrom->getDate() > $dateTo->getDate()) {
            $this->addErrorMessages(self::DATE_FROM_BEFORE_TO);
        }

        return $formModel;
    }

    /**
     * This function check the date by the following requirement:
     * - Day/Month/Year is present
     * - Day/Month/Year is a valid date
     * - Day/Month/Year is not in the future
     * - Day/Month/Year is not before 01-01-1900.
     *
     * @param DateDto $date
     * @param string  $fieldSfx
     */
    protected function validateDate(DateDto $date, $fieldSfx)
    {
        if ($date === null || empty($date->getDay()) || empty($date->getMonth()) || empty($date->getYear())) {
            $this->addErrorMessages(sprintf(self::DATE_MISSING, $fieldSfx));
        } elseif ($date->getDate() === null) {
            $this->addErrorMessages(
                sprintf(
                    self::DATE_INVALID,
                    $fieldSfx, $date->getDay() . '-' . $date->getMonth() . '-' . $date->getYear()
                )
            );
        } elseif (DateUtils::isDateInFuture($date->getDate()) === true) {
            $this->addErrorMessages(
                sprintf(self::DATE_RANGE_INVALID, $fieldSfx, $date->getDate()->format(self::DATE_FORMAT))
            );
        } elseif ($date->getDate() < new \DateTime(self::DATE_BEFORE)) {
            $this->addErrorMessages(
                sprintf(self::DATE_RANGE_INVALID, $fieldSfx, $date->getDate()->format(self::DATE_FORMAT))
            );
        }
    }

    /**
     * This function return the organisation Dto.
     *
     * @param int    $organisationId
     * @param string $type
     *
     * @return \DvsaCommon\Dto\Organisation\OrganisationDto|null
     */
    protected function getOrganisation($organisationId, $type)
    {
        if ($type !== 'ae') {
            return null;
        }
        try {
            return $this->getMapperFactory()->Organisation->getAuthorisedExaminer($organisationId);
        } catch (RestApplicationException $e) {
            $this->addErrorMessages($e->getDisplayMessages());
        }

        return null;
    }

    /**
     * This function return an array of the site information.
     *
     * @param int    $siteId
     * @param string $type
     *
     * @return VehicleTestingStationDto|null
     */
    protected function getSite($siteId, $type)
    {
        if ($type != 'site') {
            return null;
        }

        try {
            return $this->getMapperFactory()->Site->getById($siteId);
        } catch (RestApplicationException $e) {
            $this->addErrorMessages($e->getDisplayMessages());
        }

        return null;
    }

    /**
     * This function return the information about a person.
     *
     * @param int    $personId
     * @param string $type
     *
     * @return \DvsaClient\Entity\Person|null
     */
    protected function getPerson($personId, $type)
    {
        if ($type != 'person') {
            return null;
        }

        try {
            return $this->getMapperFactory()->Person->getById($personId);
        } catch (RestApplicationException $e) {
            $this->addErrorMessages($e->getDisplayMessages());
        }

        return null;
    }

    /**
     * This function return the list of the events linked to an entity.
     *
     * @param int          $id
     * @param string       $type
     * @param EventFormDto $formDto
     *
     * @return \DvsaCommon\Dto\Event\EventListDto|null
     */
    protected function getEvents($id, $type, $formDto)
    {
        $dtoHydrator = new DtoHydrator();
        $form = $dtoHydrator->extract($formDto);

        try {
            return $this->getMapperFactory()->Event->getEventList($id, $type, $form);
        } catch (RestApplicationException $e) {
            $this->addErrorMessages($e->getDisplayMessages());
        }

        return null;
    }

    /**
     * This is the common action who allow us to get the information about an event.
     *
     * @return Response|ViewModel
     */
    public function detailAction()
    {
        $id = $this->params()->fromRoute('id');
        $eventId = $this->params()->fromRoute('event-id');
        $type = $this->params()->fromRoute('type');

        if ($type === 'ae') {
            $thisTitle = 'AE';
        } else {
            $thisTitle = ucfirst($type);
        }

        $this->layout()->setVariable('pageSubTitle', 'Events');
        $this->layout()->setVariable('pageTitle', 'Event details');

        /*
        $breadcrumbs = [
            'Events for [Entity name]' => '[entity_events_list__page_URL]',
            'Events detail'            => '',
        ];
        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);
        */

        $this->layout('layout/layout-govuk.phtml');
        $this->setHeadTitle('Event details');

        if ($this->getAuthorizationService()->isGranted(PermissionInSystem::EVENT_READ) === false) {
            return $this->redirect()->toUrl(PersonUrlBuilderWeb::home());
        }

        // get the previous route from url until /event/list is refactored to new profile
        $previousRoute = urldecode($this->getRequest()->getQuery(self::PERSON_PROFILE_GO_BACK_PARAMETER));

        /* @var Request $request */
        $request = $this->getRequest();
        $formData = $request->getQuery()->toArray();

        $viewModel = new EventDetailViewModel(
            $this->getOrganisation($id, $type),
            $this->getSite($id, $type),
            $this->getPerson($id, $type),
            $this->getEventDetails($eventId),
            $type,
            new EventFormDto($formData),
            $previousRoute
        );

        return (new ViewModel(
            [
                'viewModel' => $viewModel,
            ]
        ))->setTemplate('event/event/details.phtml');
    }

    /**
     * @param $id
     *
     * @return \DvsaCommon\Dto\Event\EventDto|void
     */
    protected function getEventDetails($id)
    {
        try {
            return $this->getMapperFactory()->Event->getEvent($id);
        } catch (RestApplicationException $e) {
            $this->addErrorMessages($e->getDisplayMessages());
        }

        return null;
    }
}
