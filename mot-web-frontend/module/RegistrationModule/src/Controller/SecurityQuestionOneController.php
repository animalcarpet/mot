<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\RegistrationModule\Controller;

use Dvsa\Mot\Frontend\RegistrationModule\Step\SecurityQuestionOneStep;

/**
 * SecurityQuestion Controller.
 */
class SecurityQuestionOneController extends AbstractRegistrationController
{
    const PAGE_TITLE = 'First security question';

    public function indexAction()
    {
        return $this->doStepLogic(SecurityQuestionOneStep::STEP_ID, self::PAGE_TITLE, self::DEFAULT_SUB_TITLE);
    }
}
