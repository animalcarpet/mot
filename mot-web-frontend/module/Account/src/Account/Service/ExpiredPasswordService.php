<?php

namespace Account\Service;

use Core\Service\MotFrontendIdentityProviderInterface;
use DvsaClient\Mapper\ExpiredPasswordMapper;
use DvsaCommon\Configuration\MotConfig;
use DvsaCommon\Date\DateUtils;

class ExpiredPasswordService
{
    private $identityProvider;

    private $config;

    private $mapper;

    public function __construct(
        MotFrontendIdentityProviderInterface $identityProvider,
        MotConfig $config,
        ExpiredPasswordMapper $mapper
    ) {
        $this->identityProvider = $identityProvider;
        $this->config = $config;
        $this->mapper = $mapper;
    }

    public function calculatePasswordChangePromptDate($expirationDate)
    {
        $expirationDate = DateUtils::roundUp($expirationDate);

        $expirationDate = $expirationDate->modify('- '.$this->getGracePeriod());

        return $expirationDate;
    }

    public function sentExpiredPasswordNotificationIfNeeded($token, $passwordExpiryDate)
    {
        if ($this->isExpiryPasswordEnabled()) {
            if ($this->willUserPasswordExpireShortly($passwordExpiryDate)) {
                $passwordChangePromptDate = $this->calculatePasswordChangePromptDate($passwordExpiryDate);
                $this->mapper->postPasswordExpiredDate($token, $passwordChangePromptDate);
            }
        }
    }

    private function isExpiryPasswordEnabled()
    {
        return $this->config->get('feature_toggle', 'openam.password.expiry.enabled');
    }

    private function willUserPasswordExpireShortly($passwordExpiryDate)
    {
        $longestNotificationPeriod = $this->getLongestPeriod();

        if ($longestNotificationPeriod === null) {
            return false;
        }

        $expiryDate = $this->calculatePasswordChangePromptDate($passwordExpiryDate);
        $now = new \DateTime();
        $whenToSendNotifications = clone $expiryDate;
        $whenToSendNotifications = $whenToSendNotifications->modify('- '.$longestNotificationPeriod.'days');

        if ($now > $expiryDate) {
            return false;
        }

        return $now >= $whenToSendNotifications;
    }

    private function getLongestPeriod()
    {
        $periods = $this->config->get('password_expiry_notification_days');
        if (!$periods) {
            return null;
        }

        return max($periods);
    }

    private function getGracePeriod()
    {
        return $this->config->get('password_expiry_grace_period');
    }
}
