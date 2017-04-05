<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Event\Controller;

use DvsaCommon\InputFilter\Event\OutcomeInputFilter;
use DvsaCommon\InputFilter\Event\RecordInputFilter;
use Event\Step\SummaryStep;
use Zend\View\Model\ViewModel;

/**
 * EventSummary Controller.
 */
class EventSummaryController extends EventBaseController
{
    const PAGE_TITLE = 'Event summary';

    /**
     * @return mixed|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $this->extractRouteParams();
        $this->loadEventCatalogData();
        $this->loadEventCategory();

        $this->assertPermission();

        $this->stepService->injectParamsIntoSteps($this->getType(), $this->getId());

        $viewModel = $this->doStepLogic(SummaryStep::STEP_ID, self::PAGE_TITLE, $this->getSubtitleByType($this->getType()));

        if ($viewModel instanceof ViewModel) {
            $viewModel = $this->injectViewModelVariables($viewModel);
            $viewModel->setVariable('eventDate', $this->makeDate($viewModel));

            $viewModel->setVariable(
                'eventTypeName',
                $this->getEventTypeName(
                    $this->eventTypesWithOutcomes[$this->eventCategory],
                    $viewModel->getVariable(RecordInputFilter::FIELD_TYPE)
                )
            );

            $viewModel->setVariable(
                'eventOutcomeCodeName',
                $this->getEventOutcomeName(
                    $this->eventTypesWithOutcomes[$this->eventCategory],
                    $viewModel->getVariable(RecordInputFilter::FIELD_TYPE),
                    $viewModel->getVariable(OutcomeInputFilter::FIELD_OUTCOME)
                )
            );
        }

        $this->setHeadTitle('Event summary');

        return $viewModel;
    }

    /**
     * @param \Zend\View\Model\ViewModel $viewModel
     *
     * @return string
     */
    public function makeDate(ViewModel $viewModel)
    {
        $dt =  new \DateTime(
            $viewModel->getVariable('month') . '/' .
            $viewModel->getVariable('day') . '/' .
            $viewModel->getVariable('year')
        );

        return $dt->format("jS F Y");
    }

    /**
     * Convert an event type code into its actual name.
     *
     * @param $eventTypes
     * @param $eventType
     */
    protected function getEventTypeName($eventTypes, $eventType)
    {
        foreach ($eventTypes as $type) {
            if ($type['code'] === $eventType) {
                return $type['name'];
            }
        }

        return;
    }

    /**
     * Convert an outcome code into its actual name.
     *
     * @param $eventTypes
     * @param $eventType
     * @param $outcome
     */
    protected function getEventOutcomeName($eventTypes, $eventType, $outcome)
    {
        foreach ($eventTypes as $type) {
            if ($type['code'] === $eventType) {
                foreach ($type['outcomes'] as $eventOutcome) {
                    if ($eventOutcome['code'] == $outcome) {
                        return $eventOutcome['name'];
                    }
                }
            }
        }

        return;
    }
}
