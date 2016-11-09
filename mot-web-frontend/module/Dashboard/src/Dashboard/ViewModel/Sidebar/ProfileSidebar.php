<?php
namespace Dashboard\ViewModel\Sidebar;

use Core\ViewModel\Sidebar\GeneralSidebar;
use Core\ViewModel\Sidebar\GeneralSidebarLink;
use Core\ViewModel\Sidebar\GeneralSidebarLinkList;

class ProfileSidebar extends GeneralSidebar
{
    /**
     * @param int $personId
     */
    public function __construct($personId)
    {
        $htmlId = 'roles-and-associations-link';
        $text = 'Roles and associations';

        $url = '/your-profile/' . $personId . '/trade-roles';

        $relatedList = new GeneralSidebarLinkList('Related');
        $relatedList->addLink(new GeneralSidebarLink($htmlId, $text, $url));

        $this->addItem($relatedList);
    }
}
