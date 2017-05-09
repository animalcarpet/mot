<?php

namespace UserApi\Application\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Service\Exception\ForbiddenException;
use UserApi\Application\Service\ApplicationService;
use Zend\View\Model\JsonModel;

/**
 * Class ApplicationController.
 */
class ApplicationController extends AbstractDvsaRestfulController
{
    public function getList()
    {
        $userIdFromRoute = $this->params()->fromRoute('userId');
        $this->confirmUserIdentity($userIdFromRoute);

        $service = $this->getApplicationService();

        return new JsonModel(['data' => $service->getApplicationsForUser($userIdFromRoute)]);
    }

    private function confirmUserIdentity($userId)
    {
        $identity = $this->getIdentity();

        if ($identity === null || $userId !== strval($identity->getUserId())) {
            throw new ForbiddenException('Access denied.');
        }
    }

    /**
     * @return ApplicationService
     */
    private function getApplicationService()
    {
        return $this->getServiceLocator()->get(ApplicationService::class);
    }
}
