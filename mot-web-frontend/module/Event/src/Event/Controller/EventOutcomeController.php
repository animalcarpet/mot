<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Event\Controller;

use DvsaCommon\InputFilter\Event\RecordInputFilter;
use Event\Step\OutcomeStep;
use Event\Step\RecordStep;
use Zend\View\Model\ViewModel;

/**
 * EventOutcome Controller.
 */
class EventOutcomeController extends EventBaseController
{
    const PAGE_TITLE = 'Record an event outcome';

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

        $viewModel = $this->doStepLogic(OutcomeStep::STEP_ID, self::PAGE_TITLE, $this->getSubtitleByType($this->getType()));

        if ($viewModel instanceof ViewModel) {
            $viewModel = $this->injectViewModelVariables($viewModel);
            $viewModel->setVariable(
                RecordInputFilter::FIELD_TYPE,
                $this->stepService->getById(RecordStep::STEP_ID)->load()->getEventType()
            );
        }

        $this->setHeadTitle('Record an event outcome');

        return $viewModel;
    }
}
