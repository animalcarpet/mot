<?php

namespace Site\Controller;

use DvsaClient\Entity\SiteDailyOpeningHours;
use Core\Controller\AbstractAuthActionController;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Date\Time;
use DvsaCommon\Dto\Site\SiteTestingDailyScheduleDto;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\HttpRestJson\Exception\ValidationException;
use Site\Traits\SiteServicesTrait;

/**
 * Class SiteTestingDailyScheduleController.
 */
class SiteTestingDailyScheduleController extends AbstractAuthActionController
{
    use SiteServicesTrait;

    const ROUTE_SITE_OPENING_HOURS = 'site/edit-opening-hours';

    public function editAction()
    {
        $errorData = [];
        $siteId = $this->params()->fromRoute('siteId');

        $this->getAuthorizationService()->assertGrantedAtSite(PermissionAtSite::TESTING_SCHEDULE_UPDATE, $siteId);

        $mapperFactory = $this->getMapperFactory();
        $site = $mapperFactory->Site->getById($siteId);
        $vtsName = $site->getName();

        $data = $site->getSiteTestingSchedule();
        $data = $this->extractOpeningHours($data);

        $this->setHeadTitle('Change testing hours');

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost()->toArray();
            try {
                $mapperFactory->VehicleTestingStationOpeningHours->update($siteId, $data);
                $this->addInfoMessages('Opening times successfully updated');

                return $this->redirect()->toRoute('vehicle-testing-station', ['id' => $siteId]);
            } catch (ValidationException $e) {
                $this->addErrorMessages($e->getDisplayMessages());
                $errorData = $e->getExpandedErrorData();
                $this->addFormErrorMessagesToSession($e->getFormErrorDisplayMessages());
            } catch (RestApplicationException $e) {
                $this->addErrorMessages($e->getDisplayMessages());
            }
        }

        return [
            'siteId' => $siteId,
            'siteOpeningHours' => $data,
            'errorData' => $errorData,
            'vtsName' => $vtsName,
        ];
    }

    /**
     * @param SiteTestingDailyScheduleDto[] $data
     *
     * @return array $extractedData
     */
    private function extractOpeningHours($data)
    {
        $extractedData = [];

        foreach ($data as $day) {
            $idPrefix = strtolower(SiteDailyOpeningHours::$DAY_NAMES[$day->getWeekday()]);
            $isClosed = $day->getOpenTime() == null || $day->getCloseTime() == null;

            $openTime = $isOpenTimeAm = $closeTime = $isCloseTimeAm = null;

            if (!$isClosed) {
                $openTime = Time::fromIso8601($day->getOpenTime())->format('g.i');
                $closeTime = Time::fromIso8601($day->getCloseTime())->format('g.i');
                $isOpenTimeAm = Time::fromIso8601($day->getOpenTime())->isAm();
                $isCloseTimeAm = Time::fromIso8601($day->getCloseTime())->isAm();
            }

            $extractedData[$idPrefix.'OpenTime'] = $openTime;
            $extractedData[$idPrefix.'OpenTimePeriod'] = $isOpenTimeAm ? 'am' : 'pm';
            $extractedData[$idPrefix.'CloseTime'] = $closeTime;
            $extractedData[$idPrefix.'CloseTimePeriod'] = $isCloseTimeAm ? 'am' : 'pm';
            $extractedData[$idPrefix.'IsClosed'] = $isClosed;
        }

        return $extractedData;
    }
}
