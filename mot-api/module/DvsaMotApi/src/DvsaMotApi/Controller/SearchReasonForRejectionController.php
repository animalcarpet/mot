<?php

namespace DvsaMotApi\Controller;

use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\ReasonForRejection\SearchReasonForRejectionInterface;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommon\Constants\FeatureToggle;
use DvsaFeature\Exception\FeatureNotAvailableException;
use DvsaFeature\FeatureToggles;
use Zend\View\Model\JsonModel;

class SearchReasonForRejectionController extends AbstractDvsaRestfulController implements AutoWireableInterface
{
    private $searchReasonForRejectionService;
    private $featureToggles;

    public function __construct(SearchReasonForRejectionInterface $searchReasonForRejectionService, FeatureToggles $featureToggles)
    {
        $this->searchReasonForRejectionService = $searchReasonForRejectionService;
        $this->featureToggles = $featureToggles;
    }

    public function getList()
    {
        if ($this->featureToggles->isEnabled(FeatureToggle::RFR_ELASTICSEARCH)) {
            throw new FeatureNotAvailableException();
        }

        $searchTerm = $this->params()->fromQuery("searchTerm", "");
        $vehicleClass = $this->params()->fromQuery("vehicleClass", "");
        $audience = $this->params()->fromQuery("audience", "");
        $page = (int) $this->params()->fromQuery("page", 1);

        $response = $this->searchReasonForRejectionService->search($searchTerm, $vehicleClass, $audience, $page);

        return $this->returnDto($response);
    }
}
