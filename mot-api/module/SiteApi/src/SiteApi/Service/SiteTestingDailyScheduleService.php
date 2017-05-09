<?php

namespace SiteApi\Service;

use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Date\Time;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaCommonApi\Transaction\TransactionAwareInterface;
use DvsaCommonApi\Transaction\TransactionAwareTrait;
use DvsaEntities\Entity\SiteTestingDailySchedule;
use DvsaEntities\Repository\SiteRepository;
use DvsaEntities\Repository\SiteTestingDailyScheduleRepository;
use SiteApi\Service\Validator\SiteTestingDailyScheduleValidator;

/**
 * Class SiteTestingDailyScheduleService.
 *
 * Provides functionality to get, manipulate and write the site weekly opening times schedule.
 */
class SiteTestingDailyScheduleService implements TransactionAwareInterface
{
    use ExtractSiteTrait, TransactionAwareTrait;

    /** @var SiteTestingDailyScheduleRepository $scheduleRepository */
    private $scheduleRepository;
    /** @var SiteRepository $siteRepository */
    private $siteRepository;

    /** @var SiteTestingDailyScheduleValidator $scheduleValidator */
    private $scheduleValidator;

    private $authorisationService;

    public function __construct(
        SiteTestingDailyScheduleRepository $scheduleRepository,
        SiteRepository $siteRepository,
        SiteTestingDailyScheduleValidator $scheduleValidator,
        MotAuthorisationServiceInterface $authorisationService
    ) {
        $this->scheduleRepository = $scheduleRepository;
        $this->siteRepository = $siteRepository;
        $this->scheduleValidator = $scheduleValidator;
        $this->authorisationService = $authorisationService;
    }

    /**
     * @param $siteId
     *
     * @return array
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function getSchedule($siteId)
    {
        $weeklySchedule = $this->scheduleRepository->findBy(['site' => $siteId]);

        if (!$weeklySchedule) {
            throw new NotFoundException('weekly schedule for site:', $siteId);
        }

        return $this->extractSchedules($weeklySchedule);
    }

    /**
     * @param $siteId
     * @param $data
     *
     * @return array
     *
     * @throws \DvsaCommonApi\Service\Exception\BadRequestException
     * @throws \DvsaCommonApi\Service\Exception\BadRequestExceptionWithMultipleErrors
     * @throws \DvsaCommonApi\Service\Exception\RequiredFieldException
     */
    public function updateSchedule($siteId, $data)
    {
        $this->authorisationService->assertGrantedAtSite(PermissionAtSite::TESTING_SCHEDULE_UPDATE, $siteId);

        $this->scheduleValidator->validateOpeningHours($data);
        $data = $data['weeklySchedule'];
        // load whole schedule, create one if does not exist
        $weeklySchedule = $this->scheduleRepository->findBy(['site' => $siteId]);
        if (empty($weeklySchedule)) {
            $weeklySchedule = $this->createNewSchedule($siteId, $data);
        } else {
            $this->updateCurrentSchedule($weeklySchedule, $data);
        }

        $this->getTransactionExecutor()->flush();

        return $this->extractSchedules($weeklySchedule);
    }

    /**
     * @param $siteId
     * @param $data
     *
     * @return SiteTestingDailySchedule[]
     */
    private function createNewSchedule($siteId, $data)
    {
        $weeklySchedule = [];
        foreach ($data as $dailyScheduleData) {
            $weeklySchedule[] = $this->scheduleRepository->createOpeningHours(
                $this->siteRepository->getReference($siteId),
                $dailyScheduleData['weekday'],
                $this->resolveOpeningTime($dailyScheduleData),
                $this->resolveClosingTime($dailyScheduleData)
            );
        }

        return $weeklySchedule;
    }

    private function resolveOpeningTime($dailyScheduleData)
    {
        return empty($dailyScheduleData['openTime']) ? null : Time::fromIso8601($dailyScheduleData['openTime']);
    }

    private function resolveClosingTime($dailyScheduleData)
    {
        return empty($dailyScheduleData['closeTime']) ? null : Time::fromIso8601($dailyScheduleData['closeTime']);
    }

    /**
     * @param SiteTestingDailySchedule[] $existingWeeklySchedule
     * @param                            $updatedWeekSchedule
     */
    private function updateCurrentSchedule($existingWeeklySchedule, $updatedWeekSchedule)
    {
        foreach ($updatedWeekSchedule as $dailyScheduleData) {
            $weekday = (int) $dailyScheduleData['weekday'];
            $dailySchedule = SiteTestingDailySchedule::getScheduleForWeekday($existingWeeklySchedule, $weekday);
            $dailySchedule
                ->setOpenTime($this->resolveOpeningTime($dailyScheduleData))
                ->setCloseTime($this->resolveClosingTime($dailyScheduleData));
        }
    }
}
