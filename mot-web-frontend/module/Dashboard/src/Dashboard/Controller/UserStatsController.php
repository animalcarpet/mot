<?php

namespace Dashboard\Controller;

use Core\Controller\AbstractAuthActionController;
use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\UrlBuilder\DashboardUrlBuilder;

/**
 * Class UserStatsController.
 */
class UserStatsController extends AbstractAuthActionController
{
    const ROUTE_USER_STATS = 'user-home/stats';

    public function showAction()
    {
        $userId = $this->getIdentity()->getUserId();
        $path = DashboardUrlBuilder::userStats($userId)->toString();
        $data = $this->getRestClient()->get($path)['data'];

        $averageTestTime = DateUtils::convertSecondsToDateInterval($data['averageTime'])->format('%hh %Im %Ss');

        $currentDate = DateTimeDisplayFormat::nowAsDate();

        return [
            'currentDate' => $currentDate,
            'today' => [
                'total' => $data['total'],
                'passed' => $data['numberOfPasses'],
                'failed' => $data['numberOfFails'],
            ],
            'currentMonth' => [
                'averageTime' => $averageTestTime,
                'failRate' => number_format($data['failRate'], 2),
            ],
        ];
    }
}
