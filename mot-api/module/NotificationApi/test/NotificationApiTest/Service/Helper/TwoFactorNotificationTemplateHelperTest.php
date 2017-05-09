<?php

namespace NotificationApiTest\Service\Helper;

use NotificationApi\Dto\Notification;
use DvsaAuthentication\TwoFactorStatus;
use NotificationApi\Service\Helper\TwoFactorNotificationTemplateHelper;
use PHPUnit_Framework_TestCase;

class TwoFactorNotificationTemplateHelperTest extends PHPUnit_Framework_TestCase
{
    const ORIGINAL_TEMPLATE = 999;

    const DIRECT_NOMINATION = true;
    const INDIRECT_NOMINATION = false;

    const TWO_FACTOR_TOGGLE_ENABLED = true;
    const TWO_FACTOR_TOGGLE_DISABLED = false;

    public function testCardActivationTemplateReturnedIfStatusIsAwaitingCardActivation()
    {
        $helper = new TwoFactorNotificationTemplateHelper(
            TwoFactorStatus::AWAITING_CARD_ACTIVATION,
            self::INDIRECT_NOMINATION,
            self::TWO_FACTOR_TOGGLE_ENABLED
        );

        $this->assertEquals(
            Notification::TEMPLATE_NOMINATION_BLOCKED_UNTIL_CARD_ACTIVATED,
            $helper->getTemplate(self::ORIGINAL_TEMPLATE)
        );
    }

    public function testCardOrderTemplateReturnedIfStatusIsAwaitingCardOrder()
    {
        $helper = new TwoFactorNotificationTemplateHelper(
            TwoFactorStatus::AWAITING_CARD_ORDER,
            self::INDIRECT_NOMINATION,
            self::TWO_FACTOR_TOGGLE_ENABLED
        );

        $this->assertEquals(
            Notification::TEMPLATE_NOMINATION_BLOCKED_UNTIL_CARD_ORDERED,
            $helper->getTemplate(self::ORIGINAL_TEMPLATE)
        );
    }

    public function testOriginalTemplateReturnedIfStatusIsActive()
    {
        $helper = new TwoFactorNotificationTemplateHelper(
            TwoFactorStatus::ACTIVE,
            self::INDIRECT_NOMINATION,
            self::TWO_FACTOR_TOGGLE_ENABLED
        );

        $this->assertEquals(
            self::ORIGINAL_TEMPLATE,
            $helper->getTemplate(self::ORIGINAL_TEMPLATE)
        );
    }

    public function testDirectCardActivationTemplateReturnedIfStatusIsAwaitingCardActivation()
    {
        $helper = new TwoFactorNotificationTemplateHelper(
            TwoFactorStatus::AWAITING_CARD_ACTIVATION,
            self::DIRECT_NOMINATION,
            self::TWO_FACTOR_TOGGLE_ENABLED
        );

        $this->assertEquals(
            Notification::TEMPLATE_DIRECT_NOMINATION_BLOCKED_UNTIL_CARD_ACTIVATED,
            $helper->getTemplate(self::ORIGINAL_TEMPLATE)
        );
    }

    public function testDirectCardOrderTemplateReturnedIfStatusIsAwaitingCardOrder()
    {
        $helper = new TwoFactorNotificationTemplateHelper(
            TwoFactorStatus::AWAITING_CARD_ORDER,
            self::DIRECT_NOMINATION,
            self::TWO_FACTOR_TOGGLE_ENABLED
        );

        $this->assertEquals(
            Notification::TEMPLATE_DIRECT_NOMINATION_BLOCKED_UNTIL_CARD_ORDERED,
            $helper->getTemplate(self::ORIGINAL_TEMPLATE)
        );
    }

    public function testDirectOriginalTemplateReturnedIfStatusIsActive()
    {
        $helper = new TwoFactorNotificationTemplateHelper(
            TwoFactorStatus::ACTIVE,
            self::DIRECT_NOMINATION,
            self::TWO_FACTOR_TOGGLE_ENABLED
        );

        $this->assertEquals(
            self::ORIGINAL_TEMPLATE,
            $helper->getTemplate(self::ORIGINAL_TEMPLATE)
        );
    }

    /**
     * @dataProvider allTwoFactorStatuses
     */
    public function testOriginalTemplateReturnedIf2faToggleIsDisabled($status, $isDirectNomination)
    {
        $helper = new TwoFactorNotificationTemplateHelper($status, $isDirectNomination, false);

        $this->assertEquals(
            self::ORIGINAL_TEMPLATE,
            $helper->getTemplate(self::ORIGINAL_TEMPLATE)
        );
    }

    public function allTwoFactorStatuses()
    {
        return [
            [TwoFactorStatus::ACTIVE, self::DIRECT_NOMINATION],
            [TwoFactorStatus::AWAITING_CARD_ACTIVATION, self::DIRECT_NOMINATION],
            [TwoFactorStatus::AWAITING_CARD_ORDER, self::DIRECT_NOMINATION],
            [TwoFactorStatus::ACTIVE, self::INDIRECT_NOMINATION],
            [TwoFactorStatus::AWAITING_CARD_ACTIVATION, self::INDIRECT_NOMINATION],
            [TwoFactorStatus::AWAITING_CARD_ORDER, self::INDIRECT_NOMINATION],
        ];
    }
}
