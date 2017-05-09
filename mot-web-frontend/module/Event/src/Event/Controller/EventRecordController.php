<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Event\Controller;

use DvsaCommon\InputFilter\Event\RecordInputFilter;
use Event\Service\EventSessionService;
use Event\Service\EventStepService;
use Event\Step\OutcomeStep;
use Event\Step\RecordStep;
use Zend\View\Model\ViewModel;

/**
 * EventRecord Controller.
 */
class EventRecordController extends EventBaseController
{
    const PAGE_TITLE = 'Record an event';

    /**
     * @var EventSessionService
     */
    protected $sessionService;

    /**
     * @param EventStepService    $stepService
     * @param EventSessionService $sessionService
     */
    public function __construct(EventStepService $stepService, EventSessionService $sessionService)
    {
        parent::__construct($stepService);

        $this->sessionService = $sessionService;
    }

    /**
     * We must have a start action so that we can destroy and previous session containers
     * that might have been unfinished.
     *
     * @return \Zend\Http\Response
     */
    public function startAction()
    {
        $this->extractRouteParams();
        $step = $this->stepService->getById(RecordStep::STEP_ID);
        $this->sessionService->destroy();

        return $this->redirect()->toRoute($step->route(), ['type' => $this->getType(), 'id' => $this->getId()]);
    }

    /**
     * @return mixed|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $this->extractRouteParams();
        $this->loadEventCatalogData();
        $this->loadEventCategory();
        $this->assertPermission();

        $previousEventType = $this->stepService->getById(RecordStep::STEP_ID)->load()->getEventType();

        $this->stepService->injectParamsIntoSteps($this->getType(), $this->getId());

        $viewModel = $this->doStepLogic(RecordStep::STEP_ID, self::PAGE_TITLE, $this->getSubtitleByType($this->getType()));

        if ($viewModel instanceof ViewModel) {
            $viewModel = $this->injectViewModelVariables($viewModel);

            if ($previousEventType !== $viewModel->getVariable(RecordInputFilter::FIELD_TYPE)) {
                $this->resetOutcomeStep();
            }
        }

        $this->setHeadTitle('Record an event');

        return $viewModel;
    }

    /**
     * Reset the Outcome step if the event has changed.
     */
    protected function resetOutcomeStep()
    {
        $outcomeStep = $this->stepService->getById(OutcomeStep::STEP_ID);
        $outcomeStep->load();
        $outcomeStep->setOutcomeCode(null);
        $outcomeStep->save(false);
    }
}
