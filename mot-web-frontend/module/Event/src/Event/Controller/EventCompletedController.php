<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Event\Controller;

use Core\Service\StepService;
use Event\Service\EventSessionService;
use Event\Service\ManualEventService;

/**
 * EventCompleted Controller.
 */
class EventCompletedController extends EventBaseController
{
    const PAGE_TITLE = 'Completed recording an event';

    /**
     * @var EventSessionService
     */
    protected $sessionService;

    /**
     * @var ManualEventService
     */
    protected $manualEventService;

    /**
     * @param StepService $stepService
     */
    public function __construct(
        StepService $stepService,
        EventSessionService $sessionService,
        ManualEventService $manualEventService
    ) {
        parent::__construct($stepService);

        $this->sessionService = $sessionService;
        $this->manualEventService = $manualEventService;
    }

    /**
     * @return \Zend\Http\Response
     */
    public function indexAction()
    {
        $this->extractRouteParams();
        $this->loadEventCatalogData();
        $this->loadEventCategory();

        try {
            $apiCallResult = $this->manualEventService->addEvent(
                $this->eventCategory,
                $this->getId(),
                $this->sessionService->toArray()
            );

            if ($apiCallResult === true) {
                $this->addSuccessMessage('A new event has been recorded.');
            } else {
                $this->addErrorMessage('The event has not been recorded, please try again.');
            }

            $this->sessionService->clear();
        } catch (\OutOfBoundsException $e) {
        }

        return $this->redirect()->toRoute('event-list', ['type' => $this->getType(), 'id' => $this->getId()]);
    }
}
