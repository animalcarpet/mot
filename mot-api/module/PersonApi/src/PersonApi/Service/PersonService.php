<?php

namespace PersonApi\Service;

use Dvsa\OpenAM\Exception\OpenAMUnauthorisedException;
use Dvsa\OpenAM\Model\OpenAMLoginDetails;
use Dvsa\OpenAM\OpenAMClientInterface;
use DvsaAuthorisation\Service\AuthorisationService;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Crypt\Hash\BCryptHashFunction;
use DvsaCommon\Crypt\Hash\HashFunctionInterface;
use DvsaCommon\Enum\BusinessRoleStatusCode;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaEntities\Repository\PersonRepository;
use DvsaMotApi\Service\TesterService;
use OrganisationApi\Service\Mapper\PersonMapper;
use DvsaEntities\Entity\Person;
use DvsaCommon\Exception\UnauthorisedException;
use Zend\Authentication\AuthenticationService;

/**
 * Class PersonService.
 */
class PersonService
{
    /**
     * @var PersonRepository
     */
    private $personRepository;

    /**
     * @var PersonMapper
     */
    private $personMapper;

    /**
     * @var OpenAMClientInterface
     */
    private $openamClient;

    /**
     * @var string
     */
    private $realm;

    /**
     * @var TesterService
     */
    private $testerService;

    /**
     * @var AuthorisationService
     */
    private $authorisationService;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @param \DvsaEntities\Repository\PersonRepository    $personRepository
     * @param \OrganisationApi\Service\Mapper\PersonMapper $personMapper
     * @param \Dvsa\OpenAM\OpenAMClientInterface           $openamClient
     * @param $realm
     * @param \DvsaMotApi\Service\TesterService               $testerService
     * @param \DvsaAuthorisation\Service\AuthorisationService $authorisationService
     * @param \Zend\Authentication\AuthenticationService
     */
    public function __construct(
        PersonRepository $personRepository,
        PersonMapper $personMapper,
        OpenAMClientInterface $openamClient,
        $realm,
        TesterService $testerService,
        AuthorisationService $authorisationService,
        AuthenticationService $authenticationService
    ) {
        $this->personRepository = $personRepository;
        $this->personMapper = $personMapper;
        $this->openamClient = $openamClient;
        $this->realm = $realm;
        $this->testerService = $testerService;
        $this->authorisationService = $authorisationService;
        $this->authenticationService = $authenticationService;
    }

    /**
     * @param $personId
     *
     * @return array
     */
    public function getPerson($personId)
    {
        $this->assertGetPersonDataGranted();
        $person = $this->personRepository->get($personId);

        return $this->personMapper->toArray($person);
    }

    /**
     * Answers the corresponding Person instance or if the object flag is FALSE then
     * we require the mapper to convert it into an Array for us and return that.
     *
     * @param $identifier
     * @param $asObject bool
     *
     * @return \DvsaEntities\Entity\Person | Array
     */
    public function getPersonByIdentifier($identifier, $asObject = true)
    {
        $this->assertGetPersonDataGranted();
        $person = $this->personRepository->getByIdentifier($identifier);

        return $asObject ? $person : $this->personMapper->toArray($person);
    }

    /**
     * Force Person by identifier to return as an Array.
     *
     * @param $identifier
     *
     * @return array
     */
    public function getPersonByIdentifierArray($identifier)
    {
        return $this->getPersonByIdentifier($identifier, false);
    }

    /**
     * @param int $personId
     *
     * @return \DvsaEntities\Entity\Person
     */
    public function getPersonById($personId)
    {
        return $this->personRepository->get($personId);
    }

    /**
     * @param string $identifier
     *
     * @return \DvsaEntities\Entity\Person
     */
    public function getPersonByIdentifierAsPerson($identifier)
    {
        return $this->personRepository->getByIdentifier($identifier);
    }

    /**
     * @param $personId
     * @param $password
     *
     * @return bool
     */
    public function validateCredentials($personId, $password)
    {
        $person = $this->getPersonById($personId);
        try {
            $wrapper = new OpenAMLoginDetails($person->getUsername(), $password, $this->realm);

            return $this->openamClient->validateCredentials($wrapper);
        } catch (OpenAMUnauthorisedException $e) {
            return false;
        }
    }

    /**
     * @param int $personId
     *
     * @return array
     */
    public function getCurrentMotTestIdByPersonId($personId)
    {
        $inProgressTestId = $this->testerService->findInProgressTestIdForTester($personId);

        return [
            'inProgressTestNumber' => $inProgressTestId,
        ];
    }

    public function regeneratePinForPerson($personId)
    {
        $pin = $this->createPin();
        $this->updatePin($personId, $pin);

        return $pin;
    }

    /**
     * Gets the site count for person as a tester.
     *
     * @param int $personId
     *
     * @return array
     */
    public function getPersonSiteCountAsTester($personId)
    {
        $siteCount = $this->personRepository->getSiteCount(
            $personId,
            SiteBusinessRoleCode::TESTER,
            BusinessRoleStatusCode::ACTIVE
        );

        return ['siteCount' => $siteCount];
    }

    /**
     * This function assert that a username is valid and return the id of the user.
     *
     * FIXME: We shouldn't rely on an assert*() methods to return any value.
     *
     * @param string $username
     *
     * @return bool|int
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function assertUsernameIsValidAndHasAnEmail($username)
    {
        $person = $this->personRepository->getByIdentifier($username);
        if ($person->getPrimaryEmail() === null) {
            return false;
        }

        return $person->getId();
    }

    /**
     * Check access for current user to fetch data of a specified person.
     *
     * @throws UnauthorisedException
     */
    private function assertGetPersonDataGranted()
    {
        $this->authorisationService->assertGranted(PermissionInSystem::PERSON_BASIC_DATA_READ);
    }

    /**
     * @return string
     *
     * Generates a new 6 digit number, pads with zero if needed
     */
    private function createPin()
    {
        return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * @param $personId
     * @param $pin
     *
     * Updates the pin stored against the person & saves
     */
    private function updatePin($personId, $pin)
    {
        $person = $this->getPersonById($personId);
        $person->setPin($this->hashPin($pin));
        $this->personRepository->save($person);
    }

    /**
     * @param $pin
     *
     * @return bool|string
     *
     * Hashes the supplied pin
     */
    private function hashPin($pin)
    {
        /** @var HashFunctionInterface $hashFunction */
        $hashFunction = new BCryptHashFunction();

        return $hashFunction->hash($pin);
    }
}
