<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use DvsaCommon\Date\Time;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteTestingDailySchedule;

/**
 * Class SiteTestingDailyScheduleRepository.
 *
 * @codeCoverageIgnore
 */
class SiteTestingDailyScheduleRepository extends AbstractMutableRepository
{
    /**
     * @param EntityManager $em
     * @param ClassMetadata $class
     */
    public function __construct($em, ClassMetadata $class)
    {
        parent::__construct($em, $class);
    }

    /**
     * @param int $id
     *
     * @return null|object
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function get($id)
    {
        $schedules = $this->find($id);

        if (null === $schedules) {
            throw new NotFoundException('Schedules not found');
        }

        return $schedules;
    }

    /**
     * @param $schedules SiteTestingDailySchedule[]
     */
    public function save($schedules)
    {
        parent::save($schedules);
    }

    /**
     * This function create the opening time of a site for a specified day of the week.
     *
     * @param Site $site
     * @param int  $weekday
     * @param Time $opening
     * @param Time $closing
     *
     * @return SiteTestingDailySchedule
     */
    public function createOpeningHours(Site $site, $weekday, $opening, $closing)
    {
        $siteTestingDailySchedule = new SiteTestingDailySchedule();

        $siteTestingDailySchedule
            ->setSite($site)
            ->setOpenTime($opening)
            ->setCloseTime($closing)
            ->setWeekday($weekday);

        $this->_em->persist($siteTestingDailySchedule);

        return $siteTestingDailySchedule;
    }
}
