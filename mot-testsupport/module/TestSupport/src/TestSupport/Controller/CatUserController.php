<?php

namespace TestSupport\Controller;

use Doctrine\ORM\EntityManager;
use TestSupport\Service\CatUserService;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;


class CatUserController extends BaseTestSupportRestfulController
{
    /**
     * @param mixed $data including
     *                    "diff" string to differentiate scheme management users
     *
     * @return void|JsonModel username of new Central Admin Team member
     */
    public function create($data)
    {
        $catUserService = $this->getServiceLocator()->get(CatUserService::class);
        $resultJson = $catUserService->create($data);
        return $resultJson;
    }
}
