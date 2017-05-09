<?php

namespace PersonApi\Service;

use DvsaCommon\Configuration\MotConfig;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaEntities\Repository\PasswordDetailRepository;

/**
 * Data for dashboard.
 */
class PasswordExpiryService
{
    /** @var $passwordExpiryNotificationService PasswordExpiryNotificationService */
    private $passwordExpiryNotificationService;

    /** @var PasswordDetailRepository */
    private $passwordDetailRepository;

    /** @var MotConfig */
    private $config;

    public function __construct(
        PasswordExpiryNotificationService $passwordExpiryNotificationService,
        PasswordDetailRepository $passwordDetailRepository,
        MotConfig $config,
        MotIdentityProviderInterface $identityProvider
    ) {
        $this->passwordExpiryNotificationService = $passwordExpiryNotificationService;
        $this->passwordDetailRepository = $passwordDetailRepository;
        $this->config = $config;
        $this->identityProvider = $identityProvider;
    }

    public function notifyUserIfPasswordExpiresSoon(array $data)
    {
        $userId = $this->identityProvider->getIdentity()->getUserId();
        $passwordExpiryDate = new \DateTime($data['expiry-date']);
        $today = new \DateTime();

        $passwordNotificationSentOn = $this->passwordDetailRepository->findPasswordNotificationSentDateByPersonId($userId);
        $daysUntilPasswordExpires = $this->getDaysUntilPasswordExpires($passwordExpiryDate);

        foreach ($this->getNotificationDays() as $days) {
            $daysToExpire = clone $passwordExpiryDate;
            $daysToExpire->sub(new \DateInterval("P{$days}D"));

            if ($daysToExpire < $today && $daysToExpire > $passwordNotificationSentOn) {
                $this->passwordExpiryNotificationService->send($userId, $daysUntilPasswordExpires);
                break;
            }
        }
    }

    private function getDaysUntilPasswordExpires(\DateTime $passwordExpiresOn)
    {
        $today = new \DateTime();
        $diff = $passwordExpiresOn->diff($today);

        return $diff->days + 1;
    }

    private function getNotificationDays()
    {
        return $this->config->get('password_expiry_notification_days');
    }
}
