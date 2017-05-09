<?php

namespace TestSupport\Service;

use Doctrine\ORM\EntityManager;
use TestSupport\Helper\DataGeneratorHelper;
use TestSupport\Helper\TestDataResponseHelper;
use TestSupport\Model\AccountPerson;
use Zend\ServiceManager\ServiceManager;

/**
 * Creates new accounts in system with different roles.
 *
 * Should not be deployed in production.
 */
class AccountDataService
{
    /**
     * @var AccountService
     */
    private $accountService;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Account Person
     */
    private $accountPerson;

    /**
     * Set the service manager object.
     *
     * @param ServiceManager $serviceManager
     */
    public function __construct(AccountService $accountService, EntityManager $entityManager)
    {
        $this->accountService = $accountService;
        $this->entityManager = $entityManager;
    }

    /**
     * @param array  $data
     * @param string $role (user role if null)
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function create($data, $role = null)
    {
        $dataGenerator = DataGeneratorHelper::buildForDifferentiator($data);
        $this->accountPerson = new AccountPerson($data, $dataGenerator);

        $account = $this->accountService->createAccount(
            $role,
            $dataGenerator, $this->accountPerson
        );

        return TestDataResponseHelper::jsonOk([
            'message' => $this->createMessage($role),
            'username' => $account->getUsername(),
            'password' => $account->getPassword(),
            'personId' => $account->getPersonId(),
            'firstName' => $this->accountPerson->getFirstName(),
            'middleName' => $this->accountPerson->getMiddleName(),
            'surname' => $this->accountPerson->getSurname(),
            'addressLine1' => $this->accountPerson->getAddressLine1(),
            'addressLine2' => $this->accountPerson->getAddressLine2(),
            'postcode' => $this->accountPerson->getPostcode(),
            'phoneNumber' => $this->accountPerson->getPhoneNumber(),
            'emailAddress' => $this->accountPerson->getEmailAddress(),
            'dateOfBirth' => $this->accountPerson->getDateOfBirth(),
            'drivingLicenceNumber' => $this->accountPerson->getDrivingLicenceNumber(),
        ]);
    }

    /**
     * @param string $role
     *
     * @return string
     */
    private function createMessage($role)
    {
        return is_null($role) ? 'User created' : strtolower(str_replace('-', ' ', $role)).' created';
    }

    public function addRole($personId, $role)
    {
        $stmt = $this->entityManager->getConnection()->prepare("
            INSERT INTO person_system_role_map (`person_id`, `person_system_role_id`, `status_id`, `created_by`)
            VALUES (
                ?,
                (SELECT `id` FROM `person_system_role` WHERE `name` = ?),
                (SELECT `id` FROM `business_role_status` WHERE `code` = 'AC'),
                1
            )
        ");

        $stmt->bindValue(1, $personId);
        $stmt->bindValue(2, $role);
        $stmt->execute();
    }

    public function addSiteRole($personId, $siteId, $role)
    {
        $stmt = $this->entityManager->getConnection()->prepare("
            INSERT INTO site_business_role_map (`site_id`, `person_id`, `site_business_role_id`, `status_id`, `created_by`)
            VALUES (
                :siteId,
                :personId,
                (SELECT `id` FROM `site_business_role` WHERE `name` = :role),
                (SELECT `id` FROM `business_role_status` WHERE `code` = 'AC'),
                1
            )
        ");

        $stmt->bindValue(':personId', $personId);
        $stmt->bindValue(':siteId', $siteId);
        $stmt->bindValue(':role', $role);
        $stmt->execute();
    }

    public function removeRole($personId, $role)
    {
        $stmt = $this->entityManager->getConnection()->prepare('
            DELETE FROM person_system_role_map
            WHERE `person_id` = ?
            AND `person_system_role_id` = (SELECT `id` FROM `person_system_role` WHERE `name` = ?)
        ');

        $stmt->bindValue(1, $personId);
        $stmt->bindValue(2, $role);
        $stmt->execute();
    }
}
