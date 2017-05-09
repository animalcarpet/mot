<?php

namespace DvsaAuthentication\Service;

use DvsaCommon\Auth\MotIdentityInterface;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\PersonRepository;
use Zend\Authentication\AuthenticationService;

class PersonProvider
{
    /**
     * @var PersonRepository
     */
    private $personRepository;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @param PersonRepository      $personRepository
     * @param AuthenticationService $authenticationService
     */
    public function __construct(PersonRepository $personRepository, AuthenticationService $authenticationService)
    {
        $this->personRepository = $personRepository;
        $this->authenticationService = $authenticationService;
    }

    /**
     * @return Person
     *
     * @throws NotFoundException
     */
    public function getPerson()
    {
        $identity = $this->authenticationService->getIdentity();

        if (!$identity instanceof MotIdentityInterface) {
            throw new \RuntimeException(
                sprintf('The authentication service is not set up with the mot identity. Found "%s".', get_class($identity))
            );
        }

        return $this->personRepository->get($identity->getUserId());
    }
}
