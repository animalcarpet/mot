<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\RegistrationModule\Controller;

use Dvsa\Mot\Frontend\RegistrationModule\Step\SecurityQuestionsStep;

/**
 * SecurityQuestions Controller.
 */
class SecurityQuestionsController extends RegistrationBaseController
{
    const PAGE_TITLE = 'Your security questions';

    public function indexAction()
    {
        $this->setHeadTitle('Your security questions');

        return $this->doStepLogic(SecurityQuestionsStep::STEP_ID, self::PAGE_TITLE, self::DEFAULT_SUB_TITLE);
    }
}
