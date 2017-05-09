<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Event\Controller;

use Core\Controller\AbstractStepController;
use DvsaCommon\Auth\PermissionInSystem;
use Event\Service\EventStepService;
use Zend\View\Model\ViewModel;

/**
 * Base class for Event Controllers.
 */
class EventBaseController extends AbstractStepController
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     *
     * Expected values: "ae", site", "person"
     */
    protected $type;

    /**
     * @var array from data catalog
     */
    protected $eventTypesWithOutcomes;

    /**
     * @var string
     *
     * Expected values: "AE", "VTS", "NT"
     */
    protected $eventCategory;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param EventStepService $eventStepService
     */
    public function __construct(EventStepService $eventStepService)
    {
        parent::__construct($eventStepService);
    }

    /**
     * @param $viewModel
     *
     * @return mixed
     */
    protected function injectViewModelVariables(ViewModel $viewModel)
    {
        $viewModel->setVariable('id', $this->getId());
        $viewModel->setVariable('type', $this->getType());
        $viewModel->setVariable('routes', $this->stepService->getRoutes());
        $viewModel->setVariable('eventTypeLookup', $this->eventTypesWithOutcomes[$this->eventCategory]);
        $viewModel->setVariable('eventCategory', $this->eventCategory);

        return $viewModel;
    }

    /**
     * Load the event category.
     */
    protected function loadEventCategory()
    {
        $this->eventCategory = $this->getEventCategoryFromType($this->getType());
    }

    /**
     * Load the event data catalog data.
     */
    protected function loadEventCatalogData()
    {
        $this->eventTypesWithOutcomes = $this->getCatalogByName('eventTypesWithOutcomes');
    }

    /**
     * @return \Core\Service\MotFrontendAuthorisationServiceInterface
     */
    protected function getAuthorizationService()
    {
        return $this->serviceLocator->get('AuthorisationService');
    }

    /**
     * @return \Zend\Http\Response
     */
    protected function assertPermission()
    {
        if (!$this->getAuthorizationService()->isGranted(PermissionInSystem::EVENT_CREATE)) {
            return $this->redirect()->toRoute('event-list', ['type' => $this->getType(), 'id' => $this->getId()]);
        }
    }

    /**
     * Convert our url 'type' param into a event_category_lookup value.
     *
     * @param $type
     *
     * @return null|string
     */
    protected function getEventCategoryFromType($type)
    {
        switch ($type) {
            case 'ae':
                return 'AE';
            case 'site':
                return 'VTS';
            case 'person':
                return 'NT';
        }

        return null;
    }

    /**
     * Get the page subtitle depending on the entity type.
     *
     * @param $type
     *
     * @return null|string
     */
    protected function getSubtitleByType($type)
    {
        switch ($type) {
            case 'ae':
                return 'AE profile';
            case 'site':
                return 'Site profile';
            case 'person':
                return 'User profile';
        }

        return null;
    }

    /**
     * Extract the route params passed tothe controller.
     */
    protected function extractRouteParams()
    {
        $this->id = $this->params()->fromRoute('id');
        $this->type = $this->params()->fromRoute('type');
    }
}
