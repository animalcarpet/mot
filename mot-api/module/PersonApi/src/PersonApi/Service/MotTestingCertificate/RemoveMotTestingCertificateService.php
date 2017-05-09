<?php

namespace PersonApi\Service\MotTestingCertificate;

use DvsaCommon\Auth\Assertion\RemoveMotTestingCertificateAssertion;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use PersonApi\Service\Mapper\TesterGroupAuthorisationMapper;
use DvsaEntities\Repository\QualificationAwardRepository;
use DvsaCommon\Database\Transaction;
use PersonApi\Service\MotTestingCertificate\Event\MotTestingCertificateEvent;
use PersonApi\Service\PersonQualificationStatusService;

class RemoveMotTestingCertificateService implements AutoWireableInterface
{
    private $assertion;
    private $personQualificationStatusService;
    private $mapper;
    private $qualificationAwardRepository;
    private $transaction;
    private $notification;
    private $event;

    public function __construct(
      RemoveMotTestingCertificateAssertion $assertion,
      PersonQualificationStatusService $personQualificationStatusService,
      TesterGroupAuthorisationMapper $mapper,
      QualificationAwardRepository $qualificationAwardRepository,
      MotTestingCertificateEvent $event,
      MotTestingCertificateNotification $notification,
      Transaction $transaction

    ) {
        $this->assertion = $assertion;
        $this->personQualificationStatusService = $personQualificationStatusService;
        $this->mapper = $mapper;
        $this->qualificationAwardRepository = $qualificationAwardRepository;
        $this->event = $event;
        $this->notification = $notification;
        $this->transaction = $transaction;
    }

    public function execute($personId, $group)
    {
        $authorisation = $this->mapper->getAuthorisation($personId);
        $this->assertion->assertGranted($personId, $group, $authorisation);

        $certificate = $this->qualificationAwardRepository->getOneByGroupAndPersonId($group, $personId);

        $this->transaction->begin();
        try {
            $this->event->sendRemoveEvent($certificate);
            $this->notification->sendRemoveNotification($certificate);
            $this->personQualificationStatusService->removeStatus(
                $certificate->getPerson(),
                $group
            );

            $this->qualificationAwardRepository->remove($certificate);
            $this->qualificationAwardRepository->flush($certificate);

            $this->transaction->commit();
        } catch (\Exception $e) {
            $this->transaction->rollback();
            throw $e;
        }
    }
}
