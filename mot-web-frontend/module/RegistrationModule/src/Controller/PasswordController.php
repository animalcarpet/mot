<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\RegistrationModule\Controller;

use Dvsa\Mot\Frontend\RegistrationModule\Step\PasswordStep;
use Zend\View\Model\ViewModel;

/**
 * Password Controller.
 */
class PasswordController extends RegistrationBaseController
{
    const PAGE_TITLE = 'Create a password';

    /**
     * @return ViewModel
     */
    public function indexAction()
    {
        $this->setHeadTitle('Create a password');
        return $this->doStepLogic(PasswordStep::STEP_ID, self::PAGE_TITLE, self::DEFAULT_SUB_TITLE);
    }
}
