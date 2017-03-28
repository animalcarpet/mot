<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\RegistrationModule\Controller;

use Dvsa\Mot\Frontend\RegistrationModule\Step\SecurityQuestionTwoStep;

/**
 * SecurityQuestion Controller.
 */
class SecurityQuestionTwoController extends RegistrationBaseController
{
    const PAGE_TITLE = 'Second security question';

    public function indexAction()
    {
        $this->setHeadTitle('Second security question');
        return $this->doStepLogic(SecurityQuestionTwoStep::STEP_ID, self::PAGE_TITLE, self::DEFAULT_SUB_TITLE);
    }
}
