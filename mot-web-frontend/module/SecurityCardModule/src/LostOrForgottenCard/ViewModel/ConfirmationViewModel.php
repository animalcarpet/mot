<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\ViewModel;

use Zend\View\Model\ViewModel;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Controller\LostOrForgottenCardController;

class ConfirmationViewModel extends ViewModel
{
    /**
     * @inheritdoc
     */
    public function __construct($variables = null, $options = null)
    {
        parent::__construct($variables, $options);
        $this->setTemplate(LostOrForgottenCardController::TEMPLATE_2FA_CONFIRMATION);
    }

    public function setShowCardOrderLink($showCardOrderLink)
    {
        $this->setVariable(LostOrForgottenCardController::VIEW_MODEL_PARAM_SHOW_CARD_ORDER_LINK, $showCardOrderLink);
    }
}