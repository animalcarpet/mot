<?php

namespace SiteApi\Controller;

use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use SiteApi\Service\SiteContactService;

/**
 * Controller which creates/update contact of VTS.
 */
class SiteContactController extends AbstractDvsaRestfulController
{
    const SITE_ID_REQUIRED_MESSAGE = 'Query parameter site Id is required';
    const SITE_ID_REQUIRED_DISPLAY_MESSAGE = 'You need to enter a Site Id to perform the search';

    public function patch($siteId, $data)
    {
        if ($siteId === null) {
            return $this->returnBadRequestResponseModel(
                self::SITE_ID_REQUIRED_MESSAGE,
                self::ERROR_CODE_REQUIRED,
                self::SITE_ID_REQUIRED_DISPLAY_MESSAGE
            );
        }

        $result = $this->getSiteContactService()->patchContactFromJson($siteId, $data);

        return ApiResponse::jsonOk($result);
    }

    /**
     * @return SiteContactService
     */
    private function getSiteContactService()
    {
        return $this->getServiceLocator()->get(SiteContactService::class);
    }
}
