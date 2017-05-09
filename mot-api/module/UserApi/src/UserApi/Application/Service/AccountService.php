<?php

namespace UserApi\Application\Service;

use Doctrine\ORM\EntityManager;
use Dvsa\OpenAM\Model\OpenAMLoginDetails;
use Dvsa\OpenAM\Model\OpenAMNewIdentity;
use Dvsa\OpenAM\OpenAMClientInterface;
use DvsaCommon\Auth\Assertion\CreateUserAccountAssertion;
use DvsaCommon\Constants\Role;
use DvsaCommon\Crypt\Hash\BCryptHashFunction;
use DvsaCommon\Crypt\Hash\HashFunctionInterface;
use DvsaCommon\Enum\BusinessRoleStatusCode;
use DvsaCommon\Enum\LicenceTypeCode;
use DvsaCommon\Enum\PersonAuthType;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Service\ContactDetailsService;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaCommonApi\Service\Exception\UserExistsException;
use DvsaEntities\Entity\AuthenticationMethod;
use DvsaEntities\Entity\BusinessRoleStatus;
use DvsaEntities\Entity\Licence;
use DvsaEntities\Entity\LicenceCountry;
use DvsaEntities\Entity\LicenceType;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\PersonSystemRole;
use DvsaEntities\Entity\PersonSystemRoleMap;
use DvsaEntities\Mapper\PersonMapper;
use DvsaEntities\Repository\AuthenticationMethodRepository;
use DvsaEntities\Repository\LicenceTypeRepository;
use PersonApi\Service\BasePersonService;
use UserApi\Application\Service\Exception\DuplicatedUserException;
use UserApi\Application\Service\Validator\AccountValidator;
use Zend\Form\Annotation\Validator;

/**
 * Service to create new user account.
 */
class AccountService extends BasePersonService
{
    /**
     * @var OpenAMClientInterface
     */
    private $openAMClient;

    /**
     * @var CreateUserAccountAssertion
     */
    private $createUserAccountAssertion;

    /**
     * @param \Doctrine\ORM\EntityManager                             $entityManager
     * @param \UserApi\Application\Service\Validator\AccountValidator $validator
     * @param \DvsaCommonApi\Service\ContactDetailsService            $contactDetailsService
     * @param \DvsaEntities\Mapper\PersonMapper                       $personMapper
     * @param \DvsaCommon\Auth\Assertion\CreateUserAccountAssertion   $createUserAccountAssertion
     * @param \Dvsa\OpenAM\OpenAMClientInterface                      $openAMClient
     * @param $realm
     * @param $xssFilter
     */
    public function __construct(
        EntityManager $entityManager,
        AccountValidator $validator,
        ContactDetailsService $contactDetailsService,
        PersonMapper $personMapper,
        CreateUserAccountAssertion $createUserAccountAssertion,
        OpenAMClientInterface $openAMClient,
        $realm,
        $xssFilter
    ) {
        parent::__construct(
            $entityManager,
            $validator,
            $contactDetailsService,
            $personMapper,
            $xssFilter
        );
        $this->createUserAccountAssertion = $createUserAccountAssertion;
        $this->openAMClient = $openAMClient;
        $this->realm = $realm;
    }

    /**
     * Creates new account in the system.
     *
     * @param array $data
     *
     * @throws DuplicatedUserException
     * @throws \Exception
     *
     * @return int
     */
    public function create($data)
    {
        $this->createUserAccountAssertion->assertGranted();

        $this->validator->validate($data);
        try {
            $em = $this->entityManager;
            $em->getConnection()->beginTransaction();

            try {
                $username = ArrayUtils::get($data, 'username');

                if (true === $this->isUsernameAlreadyUsed($username)) {
                    throw new UserExistsException();
                }

                $this->createOpenAmIdentity($data);
                $person = $this->createPersonEntity($data);
                $this->persistAndFlush($person);

                $this->addSystemRole($person, Role::USER);
                $this->createAndPersistContactDetails($data, $person);

                $em->getConnection()->commit();
            } catch (\Exception $e) {
                $em->getConnection()->rollback();
                throw $e;
            }
        } catch (UserExistsException $ue) {
            throw new DuplicatedUserException('Person '.$data['username'].' already registered');
        }

        return $person->getId();
    }

    /**
     * @param Person $person
     * @param string $roleName
     */
    private function addSystemRole($person, $roleName)
    {
        /** @var PersonSystemRole $role */
        $role = $this->entityManager->getRepository(PersonSystemRole::class)->findOneBy(
            ['name' => $roleName]
        );
        /** @var BusinessRoleStatus $status */
        $status = $this->entityManager->getRepository(BusinessRoleStatus::class)->findOneBy(
            ['code' => BusinessRoleStatusCode::ACTIVE]
        );
        $map = new PersonSystemRoleMap();
        $map->setPerson($person);
        $map->setPersonSystemRole($role);
        $map->setBusinessRoleStatus($status);
        $this->persistAndFlush($map);
    }

    /**
     * @param array $data
     *
     * @return Licence|null
     */
    private function createLicenceEntity($data)
    {
        if (empty($data['drivingLicenceNumber'])) {
            return;
        }

        $licence = new Licence();
        $licence->setLicenceType($this->getDrivingLicenceTypeEntity());
        $licence->setLicenceNumber(ArrayUtils::get($data, 'drivingLicenceNumber'));
        $licence->setCountry($this->getCountryByCode(ArrayUtils::get($data, 'drivingLicenceRegion')));

        $this->persistAndFlush($licence);

        return $licence;
    }

    /**
     * @param array $data
     *
     * @return AuthenticationMethod
     *
     * @throws NotFoundException
     */
    private function getAuthenticationMethod($data)
    {
        $code = ArrayUtils::get($data, 'authenticationMethod');

        /** @var AuthenticationMethodRepository $authMethodRepo */
        $authMethodRepo = $this->entityManager->getRepository(AuthenticationMethod::class);

        try {
            $authMethod = $authMethodRepo->getByCode($code);
        } catch (NotFoundException $e) {
            $authMethod = $authMethodRepo->getByCode(PersonAuthType::PIN);
        }

        return $authMethod;
    }

    /**
     * @param string $code
     *
     * @return LicenceCountry
     */
    private function getCountryByCode($code)
    {
        /** @var LicenceCountryRepository $countryRepo */
        $countryRepo = $this->entityManager->getRepository(LicenceCountry::class);

        $country = $countryRepo->getByCode($code);

        return $country;
    }

    /**
     * @throws NotFoundException
     *
     * @return LicenceType
     */
    private function getDrivingLicenceTypeEntity()
    {
        /** @var LicenceTypeRepository $licenceTypeRepo */
        $licenceTypeRepo = $this->entityManager->getRepository(LicenceType::class);

        /** @var LicenceType $licenceType */
        $licenceType = $licenceTypeRepo->getByCode(LicenceTypeCode::DRIVING_LICENCE);

        return $licenceType;
    }

    /**
     * @param string $username
     *
     * @return bool
     */
    private function isUsernameAlreadyUsed($username)
    {
        // Check for an existing user with username
        $user = $this->entityManager->getRepository(Person::class)->findOneBy(['username' => $username]);

        return null !== $user;
    }

    /**
     * Creates identity in OpenAM.
     *
     * @param array $data
     */
    private function createOpenAmIdentity($data)
    {
        $this->openAMClient->createIdentity(
            new OpenAMNewIdentity(
                new OpenAMLoginDetails(
                    ArrayUtils::get($data, 'username'),
                    ArrayUtils::get($data, 'password'),
                    $this->realm
                ),
                [
                    'sn' => $data['surname'],
                    'cn' => $data['firstName'].' '.$data['surname'],
                    'objectclass' => 'motUser',
                ]
            )
        );
    }

    /**
     * Creates and populates Person entity object.
     *
     * @param array $data
     *
     * @return Person
     */
    private function createPersonEntity($data)
    {
        $person = $this->createPerson($data);
        $person->setUsername(ArrayUtils::get($data, 'username'));
        $person->setDrivingLicence($this->createLicenceEntity($data));
        $person->setAccountClaimRequired(ArrayUtils::tryGet($data, 'accountClaimRequired', false));
        $person->setPasswordChangeRequired(ArrayUtils::tryGet($data, 'passwordChangeRequired', false));
        $person->setAuthenticationMethod($this->getAuthenticationMethod($data));

        if (!empty($data['pin'])) {
            /** @var HashFunctionInterface $hashFunction */
            $hashFunction = new BCryptHashFunction();
            $pinHash = $hashFunction->hash($data['pin']);

            $person->setPin($pinHash);
        }

        return $person;
    }
}
