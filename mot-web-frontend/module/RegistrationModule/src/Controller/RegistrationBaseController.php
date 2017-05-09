<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\RegistrationModule\Controller;

use Core\Controller\AbstractStepController;

class RegistrationBaseController extends AbstractStepController
{
    /** Registration journey's default subtitle */
    const DEFAULT_SUB_TITLE = 'Create an account';
}
