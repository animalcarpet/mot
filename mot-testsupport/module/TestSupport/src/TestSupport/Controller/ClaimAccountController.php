<?php

namespace TestSupport\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use DvsaCommon\Constants\OrganisationType;
use DvsaCommon\Enum\CompanyTypeName;
use DvsaCommon\UrlBuilder\UrlBuilder;
use DvsaCommon\Utility\ArrayUtils;
use TestSupport\Helper\DataGeneratorHelper;
use TestSupport\Helper\TestDataResponseHelper;
use TestSupport\Service\ClaimAccountService;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use TestSupport\Service\AEService;

/**
 * Makes a specific user require going through claim account
 */
class ClaimAccountController extends BaseTestSupportRestfulController
{
    public function create($data)
    {
        $service = $this->getServiceLocator()->get(ClaimAccountService::class);
        return TestDataResponseHelper::jsonOk($service->create($data));
    }

}
