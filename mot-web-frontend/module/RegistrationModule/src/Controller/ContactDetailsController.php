<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\RegistrationModule\Controller;

use Dvsa\Mot\Frontend\RegistrationModule\Step\ContactDetailsStep;
use Zend\View\Model\ViewModel;

/**
 * Contact Details Controller.
 */
class ContactDetailsController extends RegistrationBaseController
{
    const PAGE_TITLE = 'Your contact details';

    /**
     * @return \Zend\Http\Response|ViewModel
     */
    public function indexAction()
    {
        return $this->doStepLogic(ContactDetailsStep::STEP_ID, self::PAGE_TITLE, self::DEFAULT_SUB_TITLE);
    }
}
