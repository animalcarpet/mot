<?php

namespace PersonApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use UserApi\HelpDesk\Service\HelpDeskPersonService;

/**
 * Class PersonProfileUnrestrictedController.
 */
class PersonProfileUnrestrictedController extends AbstractDvsaRestfulController
{
    /**
     * @var HelpDeskPersonService
     */
    protected $helpDeskPersonService;

    public function __construct(HelpDeskPersonService $service)
    {
        $this->helpDeskPersonService = $service;
    }

    /**
     * @param int $personId
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function get($personId)
    {
        $person = $this->helpDeskPersonService->getPersonProfile($personId, false);

        return ApiResponse::jsonOk($person->toArray());
    }
}
