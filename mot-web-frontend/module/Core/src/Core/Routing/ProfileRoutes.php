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
     *
     * @return ProfileRoutes
     */
    public static function of($object)
    {
        return new self($object);
    }

    public function yourProfile()
    {
        return $this->url(ProfileRouteList::YOUR_PROFILE);
    }

    public function userSearch($userId)
    {
        return $this->url(ProfileRouteList::USER_SEARCH, ['id' => $userId]);
    }

    public function yourProfileTqi(array $queryParams = [])
    {
        return $this->url(ProfileRouteList::YOUR_PROFILE_TQI, [], ['query' => $queryParams]);
    }

    public function userSearchTqi($userId, array $queryParams = [])
    {
        return $this->url(ProfileRouteList::USER_SEARCH_TQI, ['id' => $userId], ['query' => $queryParams]);
    }

    public function yourProfileTqiComponentsAtSite(int $siteId, int $monthRange, string $group)
    {
        return $this->url(
            ProfileRouteList::YOUR_PROFILE_TQI_COMPONENTS_AT_SITE,
            ['site' => $siteId, 'group' => $group],
            ['query' => ['monthRange' => $monthRange]]);
    }

    public function userSearchTqiComponentsAtSite(int $userId, int $siteId, int $monthRange, string $group)
    {
        return $this->url(
            ProfileRouteList::USER_SEARCH_TQI_COMPONENTS_AT_SITE,
            ['id' => $userId, 'site' => $siteId, 'group' => $group],
            ['query' => ['monthRange' => $monthRange]]);
    }
}
