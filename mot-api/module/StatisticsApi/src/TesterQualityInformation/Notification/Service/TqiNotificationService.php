<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Notification\Service;

use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Date\DateTimeHolderInterface;
use DvsaCommon\Date\Month;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaEntities\Repository\NotificationRepository;
use DvsaEntities\Repository\PersonRepository;
use DvsaEntities\Repository\TqiRfrCountRepository;
use DvsaCommon\Database\Transaction;

abstract class TqiNotificationService implements AutoWireableInterface
{
    const FIELD_ID = "id";
    const FIELD_PERSON_ID = "personId";

    protected $personRepository;
    protected $authorisationService;
    private $dateTimeHolder;
    private $transaction;
    private $notificationRepository;
    private $tqiRfrCountRepository;
    /**
     * @var MotIdentityProviderInterface
     */
    private $identityProvider;

    public function __construct(
        PersonRepository $personRepository,
        MotAuthorisationServiceInterface $authorisationService,
        DateTimeHolderInterface $dateTimeHolder,
        Transaction $transaction,
        NotificationRepository $notificationRepository,
        TqiRfrCountRepository $tqiRfrCountRepository,
        MotIdentityProviderInterface $identityProvider
    )
    {
        $this->personRepository = $personRepository;
        $this->authorisationService = $authorisationService;
        $this->dateTimeHolder = $dateTimeHolder;
        $this->transaction = $transaction;
        $this->notificationRepository = $notificationRepository;
        $this->tqiRfrCountRepository = $tqiRfrCountRepository;
        $this->identityProvider = $identityProvider;
    }

    abstract function execute();

    public function send(array $notificationData, int $templateId, string $urlPattern)
    {
        $this->canSendNotification();
        $creatorId = $this->identityProvider->getIdentity()->getUserId();

        try {
            $this->transaction->begin();

            foreach ($notificationData as $data) {
                $recipient = $data[self::FIELD_PERSON_ID];

                $id = $data[self::FIELD_ID];
                $data["url"] = sprintf($urlPattern, $id);
                $data = $this->filterOutUnnecessaryData($data);

                $this->notificationRepository->saveNotificationWithFields($templateId, $recipient, $creatorId, $data);
            }

            $this->transaction->commit();
        } catch (\Throwable $exception) {
            $this->transaction->rollback();
            throw $exception;
        }
    }

    public function checkIfNotificationHasBeenSent(int $notificationTemplateId):bool
    {
        $date = $this->dateTimeHolder->getCurrent();
        return $this->notificationRepository->checkIfNotificationsHasBeenSent(
            $notificationTemplateId,
            (int) $date->format("Y"),
            (int) $date->format("m")
        );
    }

    private function assertTqiExists(Month $month)
    {
        $count = $this->tqiRfrCountRepository->checkIfThereAreDataForPeriod($month->getStartDate(), $month->getEndDate());
        if ($count === 0) {
            throw new \LogicException(sprintf("TQI cache has not been generated for '%s-%s'", $month->getYear(), $month->getMonth()));
        }
    }

    private function canSendNotification()
    {
        $month = $this->getMonth();
        $this->assertTqiExists($month);
    }

    private function getMonth(): Month
    {
        $date = $this->dateTimeHolder->getCurrent();
        return (new Month((int) $date->format("Y"), (int) $date->format("m")))->previous();
    }

    private function filterOutUnnecessaryData(array $data): array
    {
        unset($data[self::FIELD_PERSON_ID]);

        return $data;
    }
}
