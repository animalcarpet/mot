<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Core\Controller;

use Core\Service\StepService;
use Core\Step\Step;
use Zend\View\Model\ViewModel;

/**
 * Abstract Step Controller.
 */
abstract class AbstractStepController extends AbstractDvsaActionController
{
    /**
     * Default layout.
     */
    const DEFAULT_LAYOUT = 'layout/layout-govuk.phtml';

    /**
     * @var StepService
     */
    protected $stepService;

    /**
     * @param StepService $stepService
     */
    public function __construct(StepService $stepService)
    {
        $this->stepService = $stepService;
    }

    /**
     * @param string      $title
     * @param string|null $subtitle Do not set a default, some pages must not have a subtitle
     * @param string|null $progress
     */
    protected function setLayout($title, $subtitle = null, $progress = null)
    {
        $this->layout(self::DEFAULT_LAYOUT);
        $this
            ->layout()
            ->setVariable('pageTitle', $title)
            ->setVariable('pageSubTitle', $subtitle)
            ->setVariable('progress', $progress);
    }

    /**
     * All of our step controllers have the same logic flow to them.
     * For this reason, we have moved the logic into this abstract class.
     *
     * @param string $stepID
     * @param string $title
     * @param string $subtitle
     *
     * @throws \Exception
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function doStepLogic($stepID, $title, $subtitle = null)
    {
        $step = $this->stepService->setActiveById($stepID)->current();
        $step->load();

        if ($this->getRequest()->isPost()) {
            $step->readFromArray($this->getRequest()->getPost()->toArray());
            $step->save();

            if ($step->validate()) {
                $next = $this->stepService->next();
                if ($next instanceof Step) {
                    return $this->redirect()->toRoute(
                        $next->route(),
                        $next->routeParams()
                    );
                }
            }
        } else {
            if ($step !== $this->stepService->first()) {
                if ($this->stepService->previous()->load()->validate() === false) {
                    $previous = $this->stepService->previous();
                    if ($previous instanceof Step) {
                        return $this->redirect()->toRoute(
                            $previous->route(),
                            $previous->routeParams()
                        );
                    }
                }
            }
        }

        $this->setLayout($title, $subtitle, $step->getProgress());

        return new ViewModel($step->toViewArray());
    }
}
