<?php

namespace Application\View\Helper;

use Dashboard\Model\Dashboard;
use Dashboard\Data\ApiDashboardResource;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Utility\ArrayUtils;
use Zend\View\Helper\AbstractHelper;

/**
 * DashboardDataProvider - helper for view.
 *
 * accessible by this->dashboardDataProvider() in any *.phtml file
 */
class DashboardDataProvider extends AbstractHelper
{
    /**
     * @var MotIdentityProviderInterface
     */
    protected $identityProvider;

    /**
     * @var ApiDashboardResource
     */
    protected $apiService;

    /**
     * @param MotIdentityProviderInterface $identityProvider
     * @param ApiDashboardResource         $apiService
     */
    public function __construct(
        MotIdentityProviderInterface $identityProvider,
        ApiDashboardResource $apiService,
        MotAuthorisationServiceInterface $authorisationService
) {
        $this->identityProvider = $identityProvider;
        $this->apiService = $apiService;
        $this->authorisationService = $authorisationService;
    }

    /**
     * @return Dashboard
     */
    public function __invoke()
    {
        $identity = $this->identityProvider->getIdentity();
        if ($identity) {
            $dashboard = new Dashboard($this->apiService->get($identity->getUserId()));
            $aeList = $dashboard->getAuthorisedExaminers();

            foreach ($aeList as $ae) {
                $sites = ArrayUtils::filter($ae->getSites(), function ($site) {
                    return $this->authorisationService->isGrantedAtSite(PermissionAtSite::VEHICLE_TESTING_STATION_READ, $site->getId());
                });

                $ae->setSites($sites);
            }

            $dashboard->setAuthorisedExaminers($aeList);

            return $dashboard;
        }
    }
}
