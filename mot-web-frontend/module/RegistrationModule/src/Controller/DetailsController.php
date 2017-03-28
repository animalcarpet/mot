<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\RegistrationModule\Controller;


use Dvsa\Mot\Frontend\RegistrationModule\Step\DetailsStep;

/**
 * Details Controller.
 */
class DetailsController extends RegistrationBaseController
{
    const PAGE_TITLE = 'Your details';

    public function indexAction()
    {
        $this->setHeadTitle('Your details');
        return $this->doStepLogic(DetailsStep::STEP_ID, self::PAGE_TITLE, self::DEFAULT_SUB_TITLE);
    }
}
