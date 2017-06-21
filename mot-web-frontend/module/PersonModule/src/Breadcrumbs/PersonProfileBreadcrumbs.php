<?php

namespace Dvsa\Mot\Frontend\PersonModule\Breadcrumbs;

use Application\Data\ApiPersonalDetails;
use Dashboard\Model\PersonalDetails;
use Dvsa\Mot\Frontend\PersonModule\Controller\PersonProfileController;
use Dvsa\Mot\Frontend\PersonModule\Security\PersonProfileGuardBuilder;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use DvsaClient\MapperFactory;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\UrlBuilder\UserAdminUrlBuilderWeb;
use Zend\Mvc\Controller\AbstractActionController;

class PersonProfileBreadcrumbs implements AutoWireableInterface
{
    private $contextProvider;
    protected $mapperFactory;
    private $personProfileGuardBuilder;
    private $apiPersonalDetails;

    const ROUTE_AE_ID = 'authorisedExaminerId';
    const ROUTE_AE = 'authorised-examiner';
    const ROUTE_VTS_ID = 'vehicleTestingStationId';
    const ROUTE_VTS = 'vehicle-testing-station';
    const ROUTE_USER_SEARCH = 'user_admin/user-search';
    const ROUTE_NEW_PROFILE = 'newProfile';

    const ROUTE_PARAM_ID = 'id';

    public function __construct(
        ContextProvider $contextProvider,
        MapperFactory $mapperFactory,
        PersonProfileGuardBuilder $personProfileGuardBuilder,
        ApiPersonalDetails $apiPersonalDetails
    ) {
        $this->contextProvider = $contextProvider;
        $this->mapperFactory = $mapperFactory;
        $this->personProfileGuardBuilder = $personProfileGuardBuilder;
        $this->apiPersonalDetails = $apiPersonalDetails;
    }

    /**
     * @param int|string $personId
     *
     * @return array
     */
    public function getBreadcrumbs($personId, AbstractActionController $controller, $currentStep = null)
    {
        $context = $this->contextProvider->getContext();

        $personalDetails = new PersonalDetails($this
            ->apiPersonalDetails
            ->getPersonalDetailsData($personId));

        $personProfileGuard = $this->personProfileGuardBuilder->createPersonProfileGuard(
            $personalDetails,
            $context
        );

        $isProfile = $personProfileGuard->isViewingOwnProfile();

        $breadcrumbs = [];
        $personName = $personalDetails->getFullName();
        $context = $this->contextProvider->getContext();

        if (ContextProvider::YOUR_PROFILE_CONTEXT === $context) {
            /*
             * Your Profile context.
             */
            $profileUrl = $controller->url()->fromRoute(self::ROUTE_NEW_PROFILE, [self::ROUTE_PARAM_ID => $personId]);

            $breadcrumbs += [PersonProfileController::CONTENT_HEADER_TYPE__YOUR_PROFILE => $profileUrl];
        } elseif (ContextProvider::USER_SEARCH_CONTEXT === $context) {
            /*
             * User search context.
             */
            $userSearchUrl = $controller->url()->fromRoute(self::ROUTE_USER_SEARCH);
            $profileUrl = $isProfile === false
                ? $controller->url()->fromRoute(ContextProvider::USER_SEARCH_PARENT_ROUTE, [self::ROUTE_PARAM_ID => $personId]) : '';

            $breadcrumbs += [PersonProfileController::CONTENT_HEADER_TYPE__USER_SEARCH => $userSearchUrl];
            $breadcrumbs += [$personName => $profileUrl];
        } elseif (ContextProvider::AE_CONTEXT === $context) {
            /*
             * AE context.
             */
            $aeId = $controller->params()->fromRoute(self::ROUTE_AE_ID);
            $ae = $this->mapperFactory->Organisation->getAuthorisedExaminer($aeId);
            $aeUrl = $controller->url()->fromRoute(self::ROUTE_AE, [self::ROUTE_PARAM_ID => $aeId]);
            $breadcrumbs += [$ae->getName() => $aeUrl];

            $profileUrl = $isProfile === false ? $controller->url()->fromRoute(ContextProvider::AE_PARENT_ROUTE, [
                self::ROUTE_AE_ID => $aeId, self::ROUTE_PARAM_ID => $personId, ]) : '';
            $breadcrumbs += [$personName => $profileUrl];
        } elseif (ContextProvider::VTS_CONTEXT === $context) {
            /*
             * VTS context.
             */
            $vtsId = $controller->params()->fromRoute(self::ROUTE_VTS_ID);
            $vts = $this->mapperFactory->Site->getById($vtsId);
            $ae = $vts->getOrganisation();

            if ($ae) {
                $aeUrl = $controller->url()->fromRoute(self::ROUTE_AE, [self::ROUTE_PARAM_ID => $ae->getId()]);
                $breadcrumbs += [$ae->getName() => $aeUrl];
            }

            $vtsUrl = $controller->url()->fromRoute(self::ROUTE_VTS, [self::ROUTE_PARAM_ID => $vtsId]);
            $breadcrumbs += [$vts->getName() => $vtsUrl];
            $profileUrl = $isProfile === false ? $controller->url()->fromRoute(ContextProvider::VTS_PARENT_ROUTE, [
                self::ROUTE_VTS_ID => $vtsId, self::ROUTE_PARAM_ID => $personId, ]) : '';
            $breadcrumbs += [$personName => $profileUrl];
        } else {
            $userSearchUrl = $this->buildUrlWithCurrentSearchQuery(UserAdminUrlBuilderWeb::of()->userSearch(), $controller);
            $profileUrl = $isProfile === false
                ? $this->buildUrlWithCurrentSearchQuery(UserAdminUrlBuilderWeb::of()->UserProfile($personId), $controller) : '';
            $breadcrumbs += [PersonProfileController::CONTENT_HEADER_TYPE__USER_SEARCH => $userSearchUrl, $personName => $profileUrl];
        }

        if (!empty($currentStep)) {
            $breadcrumbs += [$currentStep => ''];
        }

        return $breadcrumbs;
    }

    public function getRoute()
    {
        $context = $this->contextProvider->getContext();
        switch ($context) {
            case ContextProvider::YOUR_PROFILE_CONTEXT:
                return ContextProvider::YOUR_PROFILE_PARENT_ROUTE;
                break;
            case ContextProvider::USER_SEARCH_CONTEXT:
                return ContextProvider::USER_SEARCH_PARENT_ROUTE;
                break;
            case ContextProvider::AE_CONTEXT:
                return ContextProvider::AE_PARENT_ROUTE;
                break;
            case ContextProvider::VTS_CONTEXT:
                return ContextProvider::VTS_PARENT_ROUTE;
                break;
        }
    }

    /**
     * Build a url with the query params.
     *
     * @param string $url
     *
     * @return string
     */
    private function buildUrlWithCurrentSearchQuery($url, AbstractActionController $controller)
    {
        $params = $controller->getRequest()->getQuery()->toArray();
        if (empty($params)) {
            return $url;
        }

        return $url.'?'.http_build_query($params);
    }
}
