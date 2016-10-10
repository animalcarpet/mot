<?php

namespace PersonApi\Service;

use Doctrine\ORM\EntityRepository;

class DuplicateEmailCheckerService
{
    private $entityRepository;

    public function __construct(EntityRepository $emailRepository)
    {
        $this->entityRepository = $emailRepository;
    }

    public function isEmailDuplicated($email)
    {
        $result = $this->entityRepository->findBy(['email' => $email, 'isPrimary' => 1]);
        return !empty($result);
    }
}