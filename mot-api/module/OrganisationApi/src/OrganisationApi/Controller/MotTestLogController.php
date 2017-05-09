<?php

namespace OrganisationApi\Controller;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Utility\DtoHydrator;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use DvsaElasticSearch\Service\ElasticSearchService;
use DvsaEntities\DqlBuilder\SearchParam\MotTestLogSearchParam;
use OrganisationApi\Service\MotTestLogService;

/**
 * Class MotTestLogController.
 */
class MotTestLogController extends AbstractDvsaRestfulController
{
    const ERR_ORG_ID = 'Invalid organisation Id entered.';

    /** @var MotTestLogService */
    private $motTestLogService;
    /** @var ElasticSearchService */
    private $elasticSearchService;
    /** @var EntityManager */
    private $entityManager;

    public function __construct(
        MotTestLogService $motTestLogService,
        ElasticSearchService $elasticSearchService,
        EntityManager $entityManager
    ) {
        $this->motTestLogService = $motTestLogService;
        $this->elasticSearchService = $elasticSearchService;
        $this->entityManager = $entityManager;
    }

    public function summaryAction()
    {
        $orgId = $this->params()->fromRoute('id');

        return ApiResponse::jsonOk(
            $this->motTestLogService->getMotTestLogSummaryForOrganisation($orgId)
        );
    }

    public function logDataAction()
    {
        $orgId = (int) $this->params()->fromRoute('id');
        if ($orgId <= 0) {
            return $this->returnBadRequestResponseModel(
                self::ERR_ORG_ID,
                self::ERROR_CODE_REQUIRED,
                self::ERR_ORG_ID
            );
        }

        $postData = $this->processBodyContent($this->getRequest());

        $searchParams = new MotTestLogSearchParam($this->entityManager);
        $searchParams
            ->fromDto(DtoHydrator::jsonToDto($postData))
            ->setOrganisationId($orgId);

        return ApiResponse::jsonOk($this->elasticSearchService->findTestsLog($searchParams));
    }
}
