<?php

namespace Account\ViewModel\Builder;

use Dashboard\Model\HelpDeskContact;
use Dashboard\ViewModel\LockoutWarningViewModel;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Controller\LostOrForgottenCardController;
use DvsaCommon\Configuration\MotConfig;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class LockedAccountViewModelBuilder implements AutoWireableInterface
{
    const MOT_CONFIG = "helpdesk";

    private $motConfig;

    private $heading;
    private $message;
    private $backLink;
    private $backLinkText;

    public function __construct(MotConfig $motConfig)
    {
        $this->motConfig = $motConfig;
    }

    public function build(): LockoutWarningViewModel
    {
        $helpDesk = $this->motConfig->get(self::MOT_CONFIG);

        $helpDeskContact = new HelpDeskContact(
            $helpDesk[LostOrForgottenCardController::VIEW_MODEL_PARAM_HELPDESK_PHONE_NUMBER],
            $helpDesk[LostOrForgottenCardController::VIEW_MODEL_PARAM_HELPDESK_OPENING_HOURS_WEEKDAYS],
            $helpDesk[LostOrForgottenCardController::VIEW_MODEL_PARAM_HELPDESK_OPENING_HOURS_SATURDAY],
            $helpDesk[LostOrForgottenCardController::VIEW_MODEL_PARAM_HELPDESK_OPENING_HOURS_SUNDAY]
        );

        return new LockoutWarningViewModel(
            $this->heading,
            $this->message,
            $this->backLink,
            $this->backLinkText,
            $helpDeskContact
        );
    }

    public function setHeading(string $heading): LockedAccountViewModelBuilder
    {
        $this->heading = $heading;
        return $this;
    }

    public function setMessage(string $message): LockedAccountViewModelBuilder
    {
        $this->message = $message;
        return $this;
    }

    public function setBackLink(string $backLink): LockedAccountViewModelBuilder
    {
        $this->backLink = $backLink;
        return $this;
    }

    public function setBackLinkText(string $backLinkText): LockedAccountViewModelBuilder
    {
        $this->backLinkText = $backLinkText;
        return $this;
    }
}