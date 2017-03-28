<?php

namespace Dvsa\Mot\Frontend\RegistrationModule\Controller;

use Core\Service\StepService;
use Core\Step\Step;
use Dvsa\Mot\Frontend\RegistrationModule\Step\EmailStep;
use UserAdmin\Service\IsEmailDuplicateService;
use Zend\View\Model\ViewModel;

class EmailController extends RegistrationBaseController
{
    const PAGE_TITLE = 'Your email address';

    private $emailDuplicateService;

    public function __construct(StepService $stepService, IsEmailDuplicateService $emailDuplicateService)
    {
        $this->emailDuplicateService = $emailDuplicateService;
        parent::__construct($stepService);
    }

    /**
     * @return \Zend\Http\Response|ViewModel
     */
    public function indexAction()
    {
        return $this->doStepLogic(EmailStep::STEP_ID, self::PAGE_TITLE, self::DEFAULT_SUB_TITLE);
    }

    /**
     * Override of the doStepLogic function in AbstractStepController to allow for email duplication API
     * call to be done after frontend validation has taken place.
     *
     * @param string $stepID
     * @param string $title
     * @param null $subtitle
     * @return \Zend\Http\Response|ViewModel
     * @throws \Exception
     */
    public function doStepLogic($stepID, $title, $subtitle = null)
    {
        $step = $this->stepService->setActiveById($stepID)->current();
        $step->load();

        if ($this->getRequest()->isPost()) {
            $step->readFromArray($this->getRequest()->getPost()->toArray());
            $step->save();

            if ($step->validate()) {
                if (!$this->emailDuplicateService->isEmailDuplicate($step->getEmailAddress())) {
                    $next = $this->stepService->next();
                    if ($next instanceof Step) {
                        return $this->redirect()->toRoute(
                            $next->route(),
                            $next->routeParams()
                        );
                    }
                } else {
                    $this->redirect()->toRoute('account-register/duplicate-email');
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
        $this->setHeadTitle('Your email address');

        return new ViewModel($step->toViewArray());
    }
}

