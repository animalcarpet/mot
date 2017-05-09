<?php

namespace DvsaMotApi\Service;

use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use DvsaAuthorisation\Service\AuthorisationService;
use DvsaAuthorisation\Service\RoleProviderService;
use DvsaCommonApi\Service\AbstractService;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\PersonRepository;

/**
 * Class UserService.
 */
class UserService extends AbstractService
{
    /**
     * @var PersonRepository
     */
    private $userRepository;

    /**
     * @var DoctrineObject
     */
    private $objectHydrator;

    /**
     * @var RoleProviderService
     */
    protected $roleProviderService;

    /**
     * @var AuthorisationService
     */
    protected $authorisationService;
    /**
     * @param EntityManager        $entityManager
     * @param DoctrineObject       $objectHydrator
     * @param RoleProviderService  $roleProviderService
     * @param AuthorisationService $authorisationService
     */
    public function __construct(EntityManager $entityManager, DoctrineObject $objectHydrator,
                                RoleProviderService $roleProviderService,
                                AuthorisationService $authorisationService)
    {
        parent::__construct($entityManager);

        $this->userRepository = $this->entityManager->getRepository(Person::class);
        $this->objectHydrator = $objectHydrator;
        $this->roleProviderService = $roleProviderService;
        $this->authorisationService = $authorisationService;
    }

    /**
     * @return array
     */
    public function getAllUserData()
    {
        $users = $this->userRepository->findAll();

        $data = [];
        foreach ($users as $user) {
            $data[] = $this->extractUserDataWithoutPassword($user);
        }

        return $data;
    }

    /**
     * @param $userId
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     *
     * @return null|object
     */
    public function get($userId)
    {
        $user = $this->userRepository->find($userId);

        if (!$user) {
            throw new NotFoundException('Person', $userId);
        }

        return $user;
    }

    /**
     * @param $username
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     *
     * @return array
     */
    public function getUserData($username)
    {
        $user = $this->getUserByUsername($username);

        if (!$user) {
            throw new NotFoundException('Person', $username);
        }

        $userData = $this->extractUserDataWithoutPassword($user);
        $userData['roles'] = [];
        $userData['roles'] = $this->roleProviderService->getRolesForPerson($user);

        return $userData;
    }

    /**
     * @param $username
     *
     * @return null|object
     */
    public function getUserByUsername($username)
    {
        return $this->userRepository->findOneBy(['username' => $username]);
    }

    /**
     * @param $user
     *
     * @return array
     */
    protected function extractUserDataWithoutPassword($user)
    {
        $userData = $this->objectHydrator->extract($user);
        unset($userData['password']);

        return $userData;
    }
}
