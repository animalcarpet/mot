<?php

namespace Vehicle\ViewModel\Sidebar;

use Core\Routing\VehicleRoutes;
use Core\ViewModel\Sidebar\GeneralSidebar;
use Core\ViewModel\Sidebar\GeneralSidebarLink;
use Core\ViewModel\Sidebar\GeneralSidebarLinkList;
use Core\ViewModel\Sidebar\SidebarButton;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use Zend\Stdlib\ParametersInterface;
use Zend\View\Helper\Url;

class VehicleSidebar extends GeneralSidebar
{
    /**
     * VehicleSidebar constructor.
     *
     * @param Url                              $urlHelperPlugin
     * @param ParametersInterface              $searchParams
     * @param string                           $obfuscatedVehicleId
     * @param bool                             $isVehicleMasked
     * @param MotAuthorisationServiceInterface $authorisationService
     */
    public function __construct(Url $urlHelperPlugin, ParametersInterface $searchParams, $obfuscatedVehicleId,
                                $isVehicleMasked, MotAuthorisationServiceInterface $authorisationService)
    {
        if (!$isVehicleMasked
            && $authorisationService->isGranted(PermissionInSystem::ENFORCEMENT_CAN_MASK_AND_UNMASK_VEHICLES)) {
            $this->addMaskVehicleButton($urlHelperPlugin, $obfuscatedVehicleId);
        }
        $this->addMotHistoryLink($urlHelperPlugin, $searchParams, $obfuscatedVehicleId);
    }

    /**
     * @param Url    $urlHelperPlugin
     * @param string $obfuscatedVehicleId
     */
    private function addMaskVehicleButton(Url $urlHelperPlugin, $obfuscatedVehicleId)
    {
        $url = VehicleRoutes::of($urlHelperPlugin)->maskVehicle($obfuscatedVehicleId);
        $relatedList = new GeneralSidebarLinkList('Enforcement');
        $relatedList->addLink(new SidebarButton('mask-vehicle', 'Mask this vehicle', $url));
        $this->addItem($relatedList);
    }

    /**
     * @param Url                 $urlHelperPlugin
     * @param ParametersInterface $searchParams
     * @param $obfuscatedVehicleId
     */
    private function addMotHistoryLink(Url $urlHelperPlugin, ParametersInterface $searchParams, $obfuscatedVehicleId)
    {
        $htmlId = 'historyLink';
        $text = 'View MOT history';
        $url = VehicleRoutes::of($urlHelperPlugin)
            ->vehicleMotTestHistory($obfuscatedVehicleId, $searchParams->toArray());

        $relatedList = new GeneralSidebarLinkList('Related');
        $relatedList->addLink(new GeneralSidebarLink($htmlId, $text, $url));
        $this->addItem($relatedList);
    }
}
