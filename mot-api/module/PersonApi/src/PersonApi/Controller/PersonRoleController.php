<?php

namespace PersonApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use PersonApi\Service\PersonRoleService;
use DvsaCommonApi\Model\ApiResponse;

class PersonRoleController extends AbstractDvsaRestfulController
{
    /**
     * @var PersonRoleService
     */
    private $personRoleService;

    /**
     * @param PersonRoleService $personRoleService
     */
    public function __construct(PersonRoleService $personRoleService)
    {
        $this->personRoleService = $personRoleService;
    }

    /**
     * Add a role to a user.
     *
     * @var array
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function create($data)
    {
        $personSystemRoleMap = $this->personRoleService->create($this->getPersonIdFromRoute(), $data);

        return ApiResponse::jsonOk($personSystemRoleMap);
    }

    /**
     * Delete a role from a user.
     */
    public function delete($id)
    {
        $role = $this->params()->fromRoute('role', null);

        $this->personRoleService->delete($this->getPersonIdFromRoute(), $role);

        return ApiResponse::jsonOk([]);
    }

    /**
     * Get the roles available for a specific user.
     *
     * @param int $personId Person id
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function get($personId)
    {
        $roles = $this->personRoleService->getRoles($personId);

        return ApiResponse::jsonOk($roles);
    }

    /**
     * Returns the ID of the person from the route.
     *
     * @return int
     */
    private function getPersonIdFromRoute()
    {
        return (int) $this->params()->fromRoute('id');
    }
}
