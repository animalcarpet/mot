<?php

namespace PersonApiTest\Service\MotTestingCertificate;

use DvsaCommon\Auth\Assertion\RemoveMotTestingCertificateAssertion;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\QualificationAward;
use DvsaEntities\Entity\Person;
use PersonApi\Service\Mapper\TesterGroupAuthorisationMapper;
use DvsaEntities\Repository\QualificationAwardRepository;
use DvsaCommon\Database\Transaction;
use PersonApi\Service\MotTestingCertificate\Event\MotTestingCertificateEvent;
use PersonApi\Service\MotTestingCertificate\MotTestingCertificateNotification;
use PersonApi\Service\MotTestingCertificate\RemoveMotTestingCertificateService;
use DvsaCommon\Model\TesterAuthorisation;
use PersonApi\Service\PersonQualificationStatusService;

class RemoveMotTestingCertificateServiceTest extends \PHPUnit_Framework_TestCase
{
    private $assertion;
    private $personQualificationStatusService;
    private $mapper;
    private $qualificationAwardRepository;
    private $transaction;
    private $notification;
    private $event;

    public function setUp()
    {
        $this->assertion = XMock::of(RemoveMotTestingCertificateAssertion::class);
        $this->personQualificationStatusService = XMock::of(PersonQualificationStatusService::class);
        $this->mapper = XMock::of(TesterGroupAuthorisationMapper::class);
        $this->qualificationAwardRepository = XMock::of(QualificationAwardRepository::class);
        $this->transaction = XMock::of(Transaction::class);
        $this->notification = XMock::of(MotTestingCertificateNotification::class);
        $this->event = XMock::of(MotTestingCertificateEvent::class);

        $this
            ->mapper
            ->expects($this->any())
            ->method('getAuthorisation')
            ->willReturn(new TesterAuthorisation())
            ;

        $this
            ->qualificationAwardRepository
            ->expects($this->any())
            ->method('getOneByGroupAndPersonId')
            ->willReturn((new QualificationAward())->setPerson(new Person()))
        ;
    }

    public function testExecuteRemoveCertificate()
    {
        $this->assertion->expects($this->once())->method('assertGranted');
        $this->transaction->expects($this->once())->method('begin');
        $this->event->expects($this->once())->method('sendRemoveEvent');
        $this->notification->expects($this->once())->method('sendRemoveNotification');
        $this->personQualificationStatusService->expects($this->once())->method('removeStatus');
        $this->qualificationAwardRepository->expects($this->once())->method('remove');
        $this->qualificationAwardRepository->expects($this->once())->method('flush');
        $this->transaction->expects($this->exactly(1))->method('commit');

        $this->transaction->expects($this->exactly(0))->method('rollback');

        $this->createService()->execute(1, VehicleClassGroupCode::BIKES);
    }

    /**
     * @expectedException \DvsaCommon\Exception\UnauthorisedException
     */
    public function testExecuteThrowsExceptionWhenUserHasNoCorrectPermission()
    {
        $this
            ->assertion
            ->expects($this->once())
            ->method('assertGranted')
            ->willThrowException(new UnauthorisedException(''))
        ;

        $this->createService()->execute(1, VehicleClassGroupCode::BIKES);
    }

    /**
     * @expectedException \Exception
     */
    public function testExecuteRollBackChangesWhenSomethingGoesWrongDuringSendingEvent()
    {
        $this->assertion->expects($this->once())->method('assertGranted');
        $this->transaction->expects($this->once())->method('begin');

        $this
            ->event
            ->expects($this->once())
            ->method('sendRemoveEvent')
            ->willThrowException(new \Exception())
        ;

        $this->notification->expects($this->exactly(0))->method('sendRemoveNotification');
        $this->personQualificationStatusService->expects($this->exactly(0))->method('removeStatus');
        $this->qualificationAwardRepository->expects($this->exactly(0))->method('remove');
        $this->qualificationAwardRepository->expects($this->exactly(0))->method('flush');
        $this->transaction->expects($this->exactly(0))->method('commit');

        $this->transaction->expects($this->once())->method('rollback');

        $this->createService()->execute(1, VehicleClassGroupCode::BIKES);
    }

    /**
     * @expectedException \Exception
     */
    public function testExecuteRollBackChangesWhenSomethingGoesWrongDuringSendingNotification()
    {
        $this->assertion->expects($this->once())->method('assertGranted');
        $this->transaction->expects($this->once())->method('begin');
        $this->event->expects($this->once())->method('sendRemoveEvent');

        $this
            ->notification
            ->expects($this->once())
            ->method('sendRemoveNotification')
            ->willThrowException(new \Exception())
        ;

        $this->personQualificationStatusService->expects($this->exactly(0))->method('removeStatus');
        $this->qualificationAwardRepository->expects($this->exactly(0))->method('remove');
        $this->qualificationAwardRepository->expects($this->exactly(0))->method('flush');
        $this->transaction->expects($this->exactly(0))->method('commit');

        $this->transaction->expects($this->once())->method('rollback');

        $this->createService()->execute(1, VehicleClassGroupCode::BIKES);
    }

    /**
     * @expectedException \Exception
     */
    public function testExecuteRollBackChangesWhenSomethingGoesWrongDuringChangingStatus()
    {
        $this->assertion->expects($this->once())->method('assertGranted');
        $this->transaction->expects($this->once())->method('begin');
        $this->event->expects($this->once())->method('sendRemoveEvent');
        $this->notification->expects($this->once())->method('sendRemoveNotification');

        $this
            ->personQualificationStatusService
            ->expects($this->once())
            ->method('removeStatus')
            ->willThrowException(new \Exception())
        ;
        $this->qualificationAwardRepository->expects($this->exactly(0))->method('remove');
        $this->qualificationAwardRepository->expects($this->exactly(0))->method('flush');
        $this->transaction->expects($this->exactly(0))->method('commit');

        $this->transaction->expects($this->once())->method('rollback');

        $this->createService()->execute(1, VehicleClassGroupCode::BIKES);
    }

    /**
     * @expectedException \Exception
     */
    public function testExecuteRollBackChangesWhenSomethingGoesWrongDuringRemovingNotification()
    {
        $this->assertion->expects($this->once())->method('assertGranted');
        $this->transaction->expects($this->once())->method('begin');
        $this->event->expects($this->once())->method('sendRemoveEvent');
        $this->notification->expects($this->once())->method('sendRemoveNotification');
        $this->personQualificationStatusService->expects($this->exactly(1))->method('removeStatus');
        $this->qualificationAwardRepository->expects($this->once())->method('remove');
        $this->transaction->expects($this->exactly(0))->method('commit');

        $this
            ->qualificationAwardRepository
            ->expects($this->once())
            ->method('flush')
            ->willThrowException(new \Exception())
        ;

        $this->transaction->expects($this->once())->method('rollback');

        $this->createService()->execute(1, VehicleClassGroupCode::BIKES);
    }

    private function createService()
    {
        return new RemoveMotTestingCertificateService(
            $this->assertion,
            $this->personQualificationStatusService,
            $this->mapper,
            $this->qualificationAwardRepository,
            $this->event,
            $this->notification,
            $this->transaction
        );
    }
}
