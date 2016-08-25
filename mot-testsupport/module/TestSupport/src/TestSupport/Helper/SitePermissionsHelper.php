<?php

namespace TestSupport\Helper;

use Doctrine\ORM\EntityManager;
use TestSupport\Model\Account;

class SitePermissionsHelper
{
    /**
     * @var Account
     */
    private $account;

    /**
     * @var string
     */
    private $role;

    /**
     * @var  EntityManager
     */
    private $entityManager;

    /**
     * @see DvsaCommon\Enum\SiteBusinessRoleCode
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Adds the relevant role to the list of siteId's provided
     * @param Account $account
     * @param string $role
     * @param array $siteIds
     * @return void
     * @throws \Exception if the rest client responds in an unexpected fashion
     */
    public function addPermissionToSites(Account $account, $role, array $siteIds)
    {
        foreach ($siteIds as $id) {
            $stmt = $this->entityManager->getConnection()->prepare("
            INSERT INTO site_business_role_map (`site_id`, `person_id`, `site_business_role_id`, `status_id`, `created_by`)
            VALUES (
                :siteId,
                :personId,
                (SELECT `id` FROM `site_business_role` WHERE `code` = :role),
                (SELECT `id` FROM `business_role_status` WHERE `code` = 'AC'),
                1
            )
        ");

            $stmt->bindValue(':personId', $account->getPersonId());
            $stmt->bindValue(':siteId', $id);
            $stmt->bindValue(':role', $role);
            $stmt->execute();
        }
    }
}