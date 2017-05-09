<?php

namespace PersonApi\Service;

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

    private $notificationService;
    private $notificationRepository;
    private $personRepository;
    private $transaction;

    public function __construct(
        NotificationService $notificationService,
        NotificationRepository $notificationRepository,
        PersonRepository $personRepository,
        PasswordDetailRepository $passwordDetailRepository,
        Transaction $transaction
    ) {
        $this->notificationService = $notificationService;
        $this->notificationRepository = $notificationRepository;
        $this->personRepository = $personRepository;
        $this->passwordDetail = $passwordDetailRepository;
        $this->transaction = $transaction;
    }

    /**
     * @param int $personId
     * @param int $day
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
        foreach ($notifications as $notification) {
            $this->notificationRepository->remove($notification);
        }

        if (!empty($notifications)) {
            $this->notificationRepository->flush();
        }
    }
}
