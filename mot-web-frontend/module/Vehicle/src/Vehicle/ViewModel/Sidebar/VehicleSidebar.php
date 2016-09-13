<?php

namespace Vehicle\ViewModel\Sidebar;

use Core\Routing\VehicleRoutes;
use Core\ViewModel\Sidebar\GeneralSidebar;
use Core\ViewModel\Sidebar\GeneralSidebarLink;
use Core\ViewModel\Sidebar\GeneralSidebarLinkList;
use Zend\Stdlib\ParametersInterface;
use Zend\View\Helper\Url;

class VehicleSidebar extends GeneralSidebar
{
    public function __construct(Url $urlHelperPlugin, ParametersInterface $searchParams, $obfuscatedVehicleId)
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
