<?php

namespace DvsaMotApi\Controller;

use DvsaCommon\Enum\SiteTypeCode;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaMotApi\Controller\Validator\InspectionLocationValidator;
use DvsaCommonApi\Service\Exception\BadRequestException;
use SiteApi\Service\SiteService;
use Zend\View\Model\JsonModel;
use DvsaCommonApi\Model\ApiResponse;

/**
 * Class InspectionLocationController.
 */
class InspectionLocationController extends AbstractDvsaRestfulController
{
    const ERROR_MSG_EITHER_OR_LOCATION = 'Please supply a site ID or a location, not both';
    const ERROR_MSG_SITE_NUMBER_INVALID = 'Site number invalid';
    const ERROR_MSG_SITE_NUMBER_REQUIRED = 'The site number is required';

    /**
     * This will validate the request and make sure that.
     *
     *  - siteid OR location has been specified
     *  - that siteid, if given, is a real site record
     *
     * @param $data Array is the request data
     *
     * @throws BadRequestException
     *
     * @return JsonModel
     */
    public function create($data)
    {
        $return = $this->validate($data);

        $locationText = $data['location'];

        if (!empty($locationText)) {

            /** @var $motTestService \DvsaMotApi\Service\MotTestService */
            $motTestService = $this->getServiceLocator()->get('MotTestService');

            $id = $motTestService->createOffsiteComment(
                $locationText,
                $this->getUsername(),
                SiteTypeCode::OFFSITE
            );

            $jsonData = $return->getVariable('data');
            $jsonData['siteid'] = $id;

            $return->setVariable('data', $jsonData);
        }

        return $return;
    }

    /**
     * New validation handler for VALIDATE action verb.
     *
     * @param array $data POST data for validation
     *
     * @return JsonModel
     */
    public function validate($data)
    {
        $ilValidator = new InspectionLocationValidator();
        /** @var $vtsService SiteService */
        $vtsService = $this->getServiceLocator()->get(SiteService::class);
        $ilValidator->validate($data, $vtsService);

        return ApiResponse::jsonOk(
            [
                'sitename' => $ilValidator->getSiteName(),
                'siteid' => $ilValidator->getSiteId(),
                'location' => $ilValidator->getLocation(),
            ]
        );
    }
}
