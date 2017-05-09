<?php

namespace DvsaMotApi\Service;

use DvsaEntities\Repository\MotTestRepository;
use NotificationApi\Dto\Notification;
use NotificationApi\Service\NotificationService;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Service that notifies the original tester (whom started a test) of cancellation of that test by another user
 * and the reason behind it.
 */
class MotTestStatusChangeNotificationService implements ServiceLocatorAwareInterface
{
    const FIELD_VIN_OR_REG_NUMBER = 'vinOrRegNumber';
    const FIELD_NEW_STATUS = 'newStatus';
    const FIELD_USER_FULL_NAME = 'userFullName';

    protected $serviceLocator;

    /** @var \DvsaEntities\Entity\MotTest */
    protected $motTestBeforeUpdateState;

    /** @var \DvsaEntities\Entity\MotTest */
    protected $motTestAfterUpdateState;

    /** @var NotificationService */
    protected $notificationService;

    /**
     * Set service locator.
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Get service locator.
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * @return NotificationService
     */
    protected function getNotificationService()
    {
        if (is_null($this->notificationService)) {
            $this->notificationService = $this->getServiceLocator()->get(NotificationService::class);
        }

        return $this->notificationService;
    }

    public function captureMotTestBeforeUpdateStateById($motTestId)
    {
        $motTest = $this->getServiceLocator()->get(MotTestRepository::class)->getMotTestByNumber($motTestId);

        if ($motTest instanceof \DvsaEntities\Entity\MotTest) {
            $this->motTestBeforeUpdateState = clone $motTest;
        }
    }

    public function captureMotTestAfterUpdateStateByMotTestNumber($motTestNumber)
    {
        $this->motTestAfterUpdateState = $this->getServiceLocator()->get(MotTestRepository::class)
            ->getMotTestByNumber($motTestNumber);
    }

    public function sendNotificationIfApplicable()
    {
        if (!$this->motTestBeforeUpdateState instanceof \DvsaEntities\Entity\MotTest ||
            !$this->motTestAfterUpdateState instanceof \DvsaEntities\Entity\MotTest) {
            return;
        }

        $originalLastUpdatedBy = $this->motTestBeforeUpdateState->getLastUpdatedBy();
        $updatedLastUpdatedBy = $this->motTestAfterUpdateState->getLastUpdatedBy();

        if (is_null($updatedLastUpdatedBy) || $originalLastUpdatedBy === $updatedLastUpdatedBy) {
            return;
        }

        $notification = (new Notification())->setTemplate(
            Notification::TEMPLATE_MOT_TEST_STATUS_CHANGED_BY_ANOTHER_USER
        );

        $notification->setRecipient($this->motTestBeforeUpdateState->getTester());

        $registration = $this->motTestAfterUpdateState->getRegistration();

        $notification->addField(
            self::FIELD_VIN_OR_REG_NUMBER,
            empty($registration) ? $this->motTestAfterUpdateState->getVin() : $registration
        );

        $notification->addField(
            self::FIELD_USER_FULL_NAME,
            $this->motTestAfterUpdateState->getLastUpdatedBy()->getFirstName().' '.
            $this->motTestAfterUpdateState->getLastUpdatedBy()->getFamilyName()
        );

        $notification->addField(
            self::FIELD_NEW_STATUS,
            strtolower($this->motTestAfterUpdateState->getStatus())
        );

        $this->getNotificationService()->add($notification->toArray());
    }

    public function captureStateByIdAndSendNotificationIfApplicable($motTestNumber)
    {
        $this->captureMotTestAfterUpdateStateByMotTestNumber($motTestNumber);
        $this->sendNotificationIfApplicable();
    }
}
