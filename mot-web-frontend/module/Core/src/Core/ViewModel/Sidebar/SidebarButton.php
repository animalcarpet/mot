<?php

namespace Core\ViewModel\Sidebar;

class SidebarButton extends GeneralSidebarLink
{
    public function getModifier()
    {
        return 'related-button';
    }
}
