<?php

namespace Application\View\Helper;

use Application\Data\ApiUserSiteCount;
use Core\Helper\AbstractMotViewHelper;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;

/**
 * Class GetSiteCount.
 */
class GetSiteCount extends AbstractMotViewHelper
{
    protected $identity;
    protected $apiService;

    /**
     * @param ApiUserSiteCount $apiService
     * @param Identity         $identity
     */
    public function __construct(ApiUserSiteCount $apiService, Identity $identity)
    {
        $this->apiService = $apiService;
        $this->identity = $identity;
    }

    /**
     * @return string|null
     */
    public function __invoke()
    {
        $data = $this->apiService->getUserSiteCount($this->identity->getUserId());
        if (isset($data['siteCount'])) {
            return $data['siteCount'];
        }
    }
}
