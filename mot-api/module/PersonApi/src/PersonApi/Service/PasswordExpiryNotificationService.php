<?php

namespace PersonApi\Service;

use Dvsa\Mot\AuditApi\Service\HistoryAuditService;
use DvsaEntities\Entity\PasswordDetail;
use NotificationApi\Service\NotificationService;
use NotificationApi\Dto\Notification;
use DvsaEntities\Repository\NotificationRepository;
use DvsaEntities\Repository\PersonRepository;
use DvsaEntities\Repository\PasswordDetailRepository;
use DvsaCommon\Database\Transaction;

class PasswordExpiryNotificationService
{
    const EXPIRY_DAY_TOMORROW = 'tomorrow';
    const EXPIRY_IN_XX_DAYS = 'in %d days';

    /** @var NotificationService */
    private $notificationService;
    /** @var NotificationRepository */
    private $notificationRepository;
    /** @var PersonRepository */
    private $personRepository;
    /** @var PasswordDetailRepository */
    private $passwordDetail;
    /** @var Transaction */
    private $transaction;
    /** @var HistoryAuditService */
    private $historyAuditService;

    public function __construct(
        NotificationService $notificationService,
        NotificationRepository $notificationRepository,
        PersonRepository $personRepository,
        PasswordDetailRepository $passwordDetailRepository,
        Transaction $transaction,
        HistoryAuditService $historyAuditService
    ) {
        $this->notificationService = $notificationService;
        $this->notificationRepository = $notificationRepository;
        $this->personRepository = $personRepository;
        $this->passwordDetail = $passwordDetailRepository;
        $this->transaction = $transaction;
        $this->historyAuditService = $historyAuditService;
    }

    /**
     * @param int $personId
     * @param int $day
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     * @throws \Exception
     *
     * @return int
     */
    public function send($personId, $day)
    {
        $person = $this->personRepository->get($personId);
        $data = (new Notification())
            ->setRecipient($person->getId())
            ->setTemplate(Notification::TEMPLATE_PASSWORD_EXPIRY)
            ->addField('expiryDay', $this->getExpiryDay($day))
            ->addField('change_password_url', '/your-profile/change-password')
            ->toArray();

        $this->transaction->begin();

        try {
            $notificationId = $this->notificationService->add($data);

            $passwordDetail = $this->passwordDetail->findByPersonId($person->getId());
            if (is_null($passwordDetail)) {
                $passwordDetail = new PasswordDetail();
            }

            $passwordDetail
                ->setPerson($person)
                ->setPasswordNotificationSentDate(new \DateTime());

            $this->passwordDetail->save($passwordDetail);

            $this->transaction->commit();
        } catch (\Exception $e) {
            $this->transaction->rollback();
            throw $e;
        }

        return $notificationId;
    }

    /**
     * @param int $day
     *
     * @return string
     */
    private function getExpiryDay($day)
    {
        if ($day === 1) {
            return self::EXPIRY_DAY_TOMORROW;
        }

        return sprintf(self::EXPIRY_IN_XX_DAYS, $day);
    }

    /**
     * @param string $login
     */
    public function remove($login)
    {
        $person = $this->personRepository->getByIdentifier($login);
        $notifications = $this->notificationRepository->findAllByTemplateId($person->getId(), Notification::TEMPLATE_PASSWORD_EXPIRY);

        // ensure that mysql session variable @app_user_id is set for the sql triggers to execute successfully
        // this notification removal is being executed by user who is not logged in therefore ApiAuthenticationListener is not triggered
        $this->historyAuditService->setUser($person);
        $this->historyAuditService->execute();

        foreach ($notifications as $notification) {
            $this->notificationRepository->remove($notification);
        }

        if (!empty($notifications)) {
            $this->notificationRepository->flush();
        }
    }
}
