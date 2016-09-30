<?php

namespace Dvsa\Mot\Frontend\RegistrationModule\Controller;

use Dvsa\Mot\Frontend\RegistrationModule\Step\EmailStep;
use Zend\View\Model\ViewModel;

class EmailController extends RegistrationBaseController
{
    const PAGE_TITLE = 'Your email address';

    /**
     * @return \Zend\Http\Response|ViewModel
     */
    public function indexAction()
    {
        return $this->doStepLogic(EmailStep::STEP_ID, self::PAGE_TITLE, self::DEFAULT_SUB_TITLE);
    }
}

