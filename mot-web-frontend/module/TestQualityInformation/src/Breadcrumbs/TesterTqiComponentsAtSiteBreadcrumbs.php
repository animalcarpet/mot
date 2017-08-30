<?php

namespace Dvsa\Mot\Frontend\TestQualityInformation\Breadcrumbs;

use Core\Routing\PerformanceDashboardRoutes;
use Core\Routing\ProfileRoutes;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use Application\Data\ApiPersonalDetails;
use Dashboard\Model\PersonalDetails;
use Zend\View\Helper\Url;

class TesterTqiComponentsAtSiteBreadcrumbs implements AutoWireableInterface
{
    private $apiPersonalDetails;
    private $contextProvider;
    private $url;

    public function __construct(
        ApiPersonalDetails $apiPersonalDetails,
        ContextProvider $contextProvider,
        Url $url
    ) {
        $this->apiPersonalDetails = $apiPersonalDetails;
        $this->contextProvider = $contextProvider;
        $this->url = $url;
    }

    public function getBreadcrumbs($testerId, $monthRange)
    {
        $breadcrumbs = [];
        if ($this->contextProvider->isYourProfileContext()) {
            $breadcrumbs['Your profile'] = ProfileRoutes::of($this->url)->yourProfile();
            $breadcrumbs['Test quality information'] = ProfileRoutes::of($this->url)->yourProfileTqi(['monthRange' => $monthRange]);
        } else if ($this->contextProvider->isPerformanceDashboardContext()) {
            $breadcrumbs['Your performance dashboard'] = PerformanceDashboardRoutes::of($this->url)->performanceDashboard();
            $breadcrumbs['Test quality information'] = PerformanceDashboardRoutes::of($this->url)->performanceDashboardTqi(['monthRange' => $monthRange]);
        } else {
            $personalDetails = new PersonalDetails($this
                ->apiPersonalDetails
                ->getPersonalDetailsData($testerId));

            $breadcrumbs[$personalDetails->getFullName()] = ProfileRoutes::of($this->url)->userSearch($testerId);
            $breadcrumbs['Test quality information'] = ProfileRoutes::of($this->url)->userSearchTqi($testerId);
        }

        return $breadcrumbs;
    }
}
