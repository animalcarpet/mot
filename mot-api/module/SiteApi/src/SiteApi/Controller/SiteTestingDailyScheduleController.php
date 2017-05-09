<?php

namespace SiteApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use SiteApi\Service\SiteTestingDailyScheduleService;

/**
 * Class SiteTestingDailyScheduleController.
 */
class SiteTestingDailyScheduleController extends AbstractDvsaRestfulController
{
    /**
     * @param int $id
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function get($id)
    {
        return ApiResponse::jsonOk($this->getSiteTestingDailyScheduleService()->getSchedule($id));
    }

    /**
     * @return SiteTestingDailyScheduleService
     */
    private function getSiteTestingDailyScheduleService()
    {
        return $this->getServiceLocator()->get(SiteTestingDailyScheduleService::class);
    }

    /**
     * @param int   $id
     * @param array $data
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function update($id, $data)
    {
        $schedules = $this->getSiteTestingDailyScheduleService()->updateSchedule($id, $data);

        return ApiResponse::jsonOk($schedules);
    }
}
