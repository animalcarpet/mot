<?php

namespace Core\Routing;

use Zend\Mvc\Controller\AbstractController;
use Zend\View\Helper\Url;
use Zend\View\Renderer\PhpRenderer;

class ProfileRoutes extends AbstractRoutes
{
    public function __construct($urlHelper)
    {
        parent::__construct($urlHelper);
    }

    /**
     * @param Url|PhpRenderer|AbstractController|\Zend\Mvc\Controller\Plugin\Url $object
     * @return ProfileRoutes
     */
    public static function of($object)
    {
        return new ProfileRoutes($object);
    }

    public function yourProfile()
    {
        return $this->url(ProfileRouteList::YOUR_PROFILE);
    }

    public function userSearch($userId)
    {
        return $this->url(ProfileRouteList::USER_SEARCH, ["id" => $userId]);
    }

    public function yourProfileTqi($month, $year)
    {
        return $this->url(ProfileRouteList::YOUR_PROFILE_TQI, ["month" => $month, "year" => $year]);
    }

    public function userSearchTqi($userId, $month, $year)
    {
        return $this->url(ProfileRouteList::USER_SEARCH_TQI, ["id" => $userId, "month" => $month, "year" => $year]);
    }

    public function yourProfileTqiComponentsAtSite($siteId, $month, $year, $group)
    {
        return $this->url(ProfileRouteList::YOUR_PROFILE_TQI_COMPONENTS_AT_SITE, ["site" => $siteId, "month" => $month, "year" => $year, "group" => $group]);
    }

    public function userSearchTqiComponentsAtSite($userId, $siteId, $month, $year, $group)
    {
        return $this->url(ProfileRouteList::USER_SEARCH_TQI_COMPONENTS_AT_SITE, ["id" => $userId, "site" => $siteId, "month" => $month, "year" => $year, "group" => $group]);
    }
}
