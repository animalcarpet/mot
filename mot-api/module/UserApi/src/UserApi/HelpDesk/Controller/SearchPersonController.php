<?php

namespace UserApi\HelpDesk\Controller;

use DvsaCommon\Dto\Person\SearchPersonResultDto;
use DvsaCommon\Model\SearchPersonModel;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use UserApi\HelpDesk\Service\HelpDeskPersonService;

/**
 * Controller for searching user accounts in the system.
 */
class SearchPersonController extends AbstractDvsaRestfulController
{
    public function getList()
    {
        $searchPersonModel = $this->getSearchPersonData();

        $resultList = $this->getHelpDeskPersonService()->search($searchPersonModel);

        $response = [];
        /**
         * @var SearchPersonResultDto
         */
        foreach ($resultList as $person) {
            $response[] = $person->toArray();
        }

        return ApiResponse::jsonOk($response);
    }

    /**
     * @return SearchPersonModel
     */
    private function getSearchPersonData()
    {
        return new SearchPersonModel(
            $this->params()->fromQuery('username', null),
            $this->params()->fromQuery('firstName', null),
            $this->params()->fromQuery('lastName', null),
            $this->params()->fromQuery('dateOfBirth', null),
            $this->params()->fromQuery('town', null),
            $this->params()->fromQuery('postcode', null),
            $this->params()->fromQuery('email', null)
        );
    }

    /**
     * @return HelpDeskPersonService
     */
    private function getHelpDeskPersonService()
    {
        $helpDeskPersonService = $this->getServiceLocator()->get(HelpDeskPersonService::class);

        return $helpDeskPersonService;
    }
}
