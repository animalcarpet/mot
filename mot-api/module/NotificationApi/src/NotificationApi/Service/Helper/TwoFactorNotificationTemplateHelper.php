<?php

namespace NotificationApi\Service\Helper;

use DvsaAuthentication\TwoFactorStatus;
use NotificationApi\Dto\Notification;

class TwoFactorNotificationTemplateHelper
{
    private $nomineeTwoFactorStatus;

    private $isDirectNomination;

    private $isTwoFactorToggleEnabled;

    public function __construct($nomineeTwoFactorStatus, $isDirectNomination, $isTwoFactorToggleEnabled)
    {
        $this->nomineeTwoFactorStatus = $nomineeTwoFactorStatus;
        $this->isDirectNomination = $isDirectNomination;
        $this->isTwoFactorToggleEnabled = $isTwoFactorToggleEnabled;
    }

    public static function forPendingDirectNomination($nomineeTwoFactorStatus, $isTwoFactorToggleEnabled)
    {
        return new TwoFactorNotificationTemplateHelper($nomineeTwoFactorStatus, true, $isTwoFactorToggleEnabled);
    }

    public static function forPendingConditionalNomination($nomineeTwoFactorStatus, $isTwoFactorToggleEnabled)
    {
        return new TwoFactorNotificationTemplateHelper($nomineeTwoFactorStatus, false, $isTwoFactorToggleEnabled);
    }

    public function getTemplate($originalTemplate)
    {
        if (!$this->isTwoFactorToggleEnabled) {
            return $originalTemplate;
        }

        $awaitCardOrderTemplate = $this->isDirectNomination ?
            Notification::TEMPLATE_DIRECT_NOMINATION_BLOCKED_UNTIL_CARD_ORDERED :
            Notification::TEMPLATE_NOMINATION_BLOCKED_UNTIL_CARD_ORDERED;

        $awaitCardActivationTemplate = $this->isDirectNomination ?
            Notification::TEMPLATE_DIRECT_NOMINATION_BLOCKED_UNTIL_CARD_ACTIVATED :
            Notification::TEMPLATE_NOMINATION_BLOCKED_UNTIL_CARD_ACTIVATED;

        switch ($this->nomineeTwoFactorStatus) {
            case TwoFactorStatus::AWAITING_CARD_ORDER:
                $template = $awaitCardOrderTemplate;
                break;
            case TwoFactorStatus::AWAITING_CARD_ACTIVATION:
                $template = $awaitCardActivationTemplate;
                break;
            default:
                $template = $originalTemplate;
        }

        return $template;
    }
}
