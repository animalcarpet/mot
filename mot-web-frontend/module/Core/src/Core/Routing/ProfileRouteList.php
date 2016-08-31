<?php

namespace Core\Routing;

use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;

class ProfileRouteList
{
    const YOUR_PROFILE = ContextProvider::YOUR_PROFILE_PARENT_ROUTE;
    const USER_SEARCH = ContextProvider::USER_SEARCH_PARENT_ROUTE;
    const YOUR_PROFILE_TQI = "newProfile/test-quality-information";
    const USER_SEARCH_TQI = "newProfileUserAdmin/test-quality-information";
    const YOUR_PROFILE_TQI_COMPONENTS = "newProfile/test-quality-information/component-breakdown";
    const USER_SEARCH_TQI_COMPONENTS = "newProfileUserAdmin/test-quality-information/component-breakdown";
    const YOUR_PROFILE_TQI_COMPONENTS_AT_SITE = "newProfile/test-quality-information/component-breakdown-at-site";
    const USER_SEARCH_TQI_COMPONENTS_AT_SITE = "newProfileUserAdmin/test-quality-information/component-breakdown-at-site";
}
