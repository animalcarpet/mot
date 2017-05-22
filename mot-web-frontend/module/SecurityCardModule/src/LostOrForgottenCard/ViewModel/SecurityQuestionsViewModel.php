<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\ViewModel;

use Zend\Form\FormInterface;
use Zend\View\Model\ViewModel;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Controller\LostOrForgottenCardController;

class SecurityQuestionsViewModel extends ViewModel
{
    /**
     * @inheritdoc
     */
    public function __construct($variables = null, $options = null)
    {
        parent::__construct($variables, $options);
        $this->setTemplate(LostOrForgottenCardController::TEMPLATE_2FA_SECURITY_QUESTION_ONE);
    }

    /**
     * @param FormInterface $form
     */
    public function setForm(FormInterface $form)
    {
        $this->setVariable(LostOrForgottenCardController::VIEW_MODEL_PARAM_FORM, $form);
    }

    /**
     * @param string $goBackRoute
     */
    public function setGoBackRoute($goBackRoute)
    {
        $this->setVariable(LostOrForgottenCardController::VIEW_MODEL_PARAM_GO_BACK_ROUTE, $goBackRoute);
    }

    /**
     * @param string $goBackLabel
     */
    public function setGoBackLabel($goBackLabel)
    {
        $this->setVariable(LostOrForgottenCardController::VIEW_MODEL_PARAM_GO_BACK_LABEL, $goBackLabel);
    }

    /**
     * @param string $phoneNumber
     */
    public function setDvsaPhoneNumber($phoneNumber)
    {
        $this->setVariable(LostOrForgottenCardController::VIEW_MODEL_PARAM_HELPDESK_PHONE_NUMBER, $phoneNumber);
    }

    /**
     * @param string $openingHrsWeekdays
     */
    public function setDvsaOpeningHoursWeekdays($openingHrsWeekdays)
    {
        $this->setVariable(LostOrForgottenCardController::VIEW_MODEL_PARAM_HELPDESK_OPENING_HOURS_WEEKDAYS, $openingHrsWeekdays);
    }

    /**
     * @param string $openingHrsSaturday
     */
    public function setDvsaOpeningHoursSaturday($openingHrsSaturday)
    {
        $this->setVariable(LostOrForgottenCardController::VIEW_MODEL_PARAM_HELPDESK_OPENING_HOURS_SATURDAY, $openingHrsSaturday);
    }

    /**
     * @param string $openingHrsSunday
     */
    public function setDvsaOpeningHoursSunday($openingHrsSunday)
    {
        $this->setVariable(LostOrForgottenCardController::VIEW_MODEL_PARAM_HELPDESK_OPENING_HOURS_SUNDAY, $openingHrsSunday);
    }
}