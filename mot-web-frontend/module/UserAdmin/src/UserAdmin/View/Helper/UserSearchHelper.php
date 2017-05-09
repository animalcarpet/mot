<?php

namespace UserAdmin\View\Helper;

use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\UrlBuilder\UserAdminUrlBuilderWeb;

/**
 * User search view helper.
 */
class UserSearchHelper
{
    /** @var AuthorisationServiceInterface */
    protected $authService;

    /**
     * Constructor.
     */
    public function __construct($authService)
    {
        $this->authService = $authService;
    }

    public function getProfileUrl($personId)
    {
        if ($this->authService->isGranted(PermissionInSystem::VIEW_OTHER_USER_PROFILE)) {
            return UserAdminUrlBuilderWeb::userProfile($personId);
        }
    }
}
