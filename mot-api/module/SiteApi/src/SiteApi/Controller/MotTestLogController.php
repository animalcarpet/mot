<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace SiteApi\Controller;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Utility\DtoHydrator;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use DvsaElasticSearch\Service\ElasticSearchService;
use DvsaEntities\DqlBuilder\SearchParam\MotTestLogSearchParam;
use SiteApi\Service\MotTestLogService;

class MotTestLogController extends AbstractDvsaRestfulController
{
    const ERR_SITE_ID = 'Invalid site Id entered.';

    /**
     * @var MotTestLogService
     */
    private $motTestLogService;

    /**
     * @var ElasticSearchService
     */
    private $elasticSearchService;

    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(
        MotTestLogService $motTestLogService,
        ElasticSearchService $elasticSearchService,
        EntityManager $entityManager
    )
    {
        $this->motTestLogService = $motTestLogService;
        $this->elasticSearchService = $elasticSearchService;
        $this->entityManager = $entityManager;
    }

    /**
     * @return \Zend\View\Model\JsonModel
     */
    public function summaryAction()
    {
        $siteId = $this->params()->fromRoute('id');

        return ApiResponse::jsonOk(
            $this->motTestLogService->getMotTestLogSummaryForSite($siteId)
        );
    }

    /**
     * @return \Zend\View\Model\JsonModel
     */
    public function logDataAction()
    {
        $siteId = (int)$this->params()->fromRoute('id');
        if ($siteId <= 0) {
            return $this->returnBadRequestResponseModel(
                self::ERR_SITE_ID,
                self::ERROR_CODE_REQUIRED,
                self::ERR_SITE_ID
            );
        }

        $postData = $this->processBodyContent($this->getRequest());
        $motTestSearchParamsDto = DtoHydrator::jsonToDto($postData);

        $searchParams = new MotTestLogSearchParam($this->entityManager);
        $searchParams
            ->fromDto($motTestSearchParamsDto)
            ->setSiteId($siteId);

        return ApiResponse::jsonOk($this->elasticSearchService->findSiteTestsLog($searchParams));
    }
}
