<?php

namespace Dashboard\ViewHelper;

use Dashboard\Model\Notification;
use Zend\View\Helper\AbstractHelper;

class NotificationLinkViewHelper extends AbstractHelper
{
    const TEMPLATE_NOMINATION_BLOCKED_UNTIL_CARD_ORDERED = 29;
    const TEMPLATE_NOMINATION_BLOCKED_UNTIL_CARD_ACTIVATED = 30;
    const TEMPLATE_DIRECT_NOMINATION_BLOCKED_UNTIL_CARD_ORDERED = 31;
    const TEMPLATE_DIRECT_NOMINATION_BLOCKED_UNTIL_CARD_ACTIVATED = 32;

    public function __invoke(Notification $notification)
    {
        switch ($notification->getTemplateId()) {
            case self::TEMPLATE_NOMINATION_BLOCKED_UNTIL_CARD_ORDERED:
            case self::TEMPLATE_DIRECT_NOMINATION_BLOCKED_UNTIL_CARD_ORDERED:
                return '<a href="' . $this->url('security-card-order/new') . '"  id="orderCard" class="button">Order a security card</a>';
            case self::TEMPLATE_NOMINATION_BLOCKED_UNTIL_CARD_ACTIVATED:
            case self::TEMPLATE_DIRECT_NOMINATION_BLOCKED_UNTIL_CARD_ACTIVATED:
                return '<a href="' . $this->url('register-card') . '" id="activateCard" class="button">Activate your security card</a>';
            default:
                return '';
        }
    }

    private function url($routeName)
    {
        return $this->view->url($routeName);
    }
}
