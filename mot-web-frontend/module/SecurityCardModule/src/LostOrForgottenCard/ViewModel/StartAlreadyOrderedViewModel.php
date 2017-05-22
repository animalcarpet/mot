<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\ViewModel;

use Zend\View\Model\ViewModel;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Controller\LostOrForgottenCardController;

class StartAlreadyOrderedViewModel extends ViewModel
{
    /**
     * @inheritdoc
     */
    public function __construct($variables = null, $options = null)
    {
        parent::__construct($variables, $options);
        $this->setTemplate(LostOrForgottenCardController::TEMPLATE_2FA_START_ALREADY_ORDERED);
    }
}