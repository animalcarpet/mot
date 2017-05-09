<?php
/**
 * Created by PhpStorm.
 * User: szymonf
 * Date: 23.03.2016
 * Time: 15:46.
 */

namespace Dvsa\Mot\Frontend\PersonModule\Controller;

use Core\Controller\AbstractAuthActionController;
use Dashboard\Model\PersonalDetails;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use DvsaClient\MapperFactory;

class PersonProfileChildController extends AbstractAuthActionController
{
    /**
     * @var ContextProvider
     */
    private $contextProvider;
    /**
     * @var MapperFactory
     */
    private $mapperFactory;

    public function __construct(
        ContextProvider $contextProvider,
        MapperFactory $mapperFactory
    ) {
        $this->contextProvider = $contextProvider;
        $this->mapperFactory = $mapperFactory;
    }

    /**
     * @param PersonalDetails $personalDetails
     * @param int|string      $personId
     *
     * @return array
     */
    private function getBreadcrumbs(PersonalDetails $personalDetails, $personId, $isProfile, $currentPageTitle)
    {
        $breadcrumbs = [];
        $personName = $personalDetails->getFullName();
        $context = $this->contextProvider->getContext();

        if (ContextProvider::YOUR_PROFILE_CONTEXT === $context) {
            /*
             * Your Profile context.
             */
            $profileUrl = $isProfile === false ?
                $this->url()->fromRoute('newProfile', ['id' => $personId]) : '';
            $breadcrumbs += [PersonProfileController::CONTENT_HEADER_TYPE__YOUR_PROFILE => $profileUrl];
        } elseif (ContextProvider::USER_SEARCH_CONTEXT === $context) {
            /*
             * User search context.
             */
            $userSearchUrl = $this->url()->fromRoute('user_admin/user-search');
            $profileUrl = $isProfile === false ?
                $this->url()->fromRoute(ContextProvider::USER_SEARCH_PARENT_ROUTE, ['id' => $personId]) : '';

            $breadcrumbs += [PersonProfileController::CONTENT_HEADER_TYPE__USER_SEARCH => $userSearchUrl];
            $breadcrumbs += [$personName => $profileUrl];
        } elseif (ContextProvider::AE_CONTEXT === $context) {
            /*
             * AE context.
             */
            $aeId = $this->params()->fromRoute('authorisedExaminerId');
            $ae = $this->mapperFactory->Organisation->getAuthorisedExaminer($aeId);
            $aeUrl = $this->url()->fromRoute('authorised-examiner', ['id' => $aeId]);
            $breadcrumbs += [$ae->getName() => $aeUrl];

            $profileUrl = $isProfile === false ?
                $this->url()->fromRoute(ContextProvider::AE_PARENT_ROUTE, [
                    'authorisedExaminerId' => $aeId,
                    'id' => $personId,
                ]) : '';
            $breadcrumbs += [$personName => $profileUrl];
        } elseif (ContextProvider::VTS_CONTEXT === $context) {
            /*
             * VTS context.
             */
            $vtsId = $this->params()->fromRoute('vehicleTestingStationId');
            $vts = $this->mapperFactory->Site->getById($vtsId);
            $ae = $vts->getOrganisation();

            if ($ae) {
                $aeUrl = $this->url()->fromRoute('authorised-examiner', ['id' => $ae->getId()]);
                $breadcrumbs += [$ae->getName() => $aeUrl];
            }

            $vtsUrl = $this->url()->fromRoute('vehicle-testing-station', ['id' => $vtsId]);
            $breadcrumbs += [$vts->getName() => $vtsUrl];
            $profileUrl = $isProfile === false ?
                $this->url()->fromRoute(ContextProvider::VTS_PARENT_ROUTE, [
                    'vehicleTestingStationId' => $vtsId,
                    'id' => $personId,
                ]) : '';
            $breadcrumbs += [$personName => $profileUrl];
        } else {
            $userSearchUrl = $this->buildUrlWithCurrentSearchQuery(UserAdminUrlBuilderWeb::of()->userSearch());
            $profileUrl = $isProfile === false ?
                $this->buildUrlWithCurrentSearchQuery(UserAdminUrlBuilderWeb::of()->UserProfile($personId)) : '';
            $breadcrumbs += [PersonProfileController::CONTENT_HEADER_TYPE__USER_SEARCH => $userSearchUrl, $personName => $profileUrl];
        }

        $breadcrumbs += [$currentPageTitle => ''];

        return $breadcrumbs;
    }
}
