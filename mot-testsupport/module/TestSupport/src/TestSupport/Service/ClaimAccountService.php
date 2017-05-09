<?php

namespace TestSupport\Service;

use TestSupport\FieldValidation;
use Doctrine\ORM\EntityManager;

class ClaimAccountService
{
    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(
        EntityManager $em
    ) {
        $this->em = $em;
    }

    /**
     * @param mixed $data including "personId" key
     *
     * @return empty model
     */
    public function create($data)
    {
        FieldValidation::checkForRequiredFieldsInData(['personId'], $data);
        $this->setClaimAccountRequired($data['personId']);

        return [];
    }

    private function setClaimAccountRequired($personId)
    {
        $this->em->getConnection()->executeUpdate(
            'UPDATE person SET is_account_claim_required = 1 WHERE id = :id',
            ['id' => $personId]
        );

        $this->em->flush();
    }
}
