<?php

namespace AccountApi\Service;

use AccountApi\Crypt\SecurityAnswerHashFunction;
use AccountApi\Service\Exception\OpenAmChangePasswordException;
use AccountApi\Service\Validator\ClaimValidator;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Constants\EventDescription;
use DvsaCommon\Constants\PersonContactType as PersonContactTypeEnum;
use DvsaCommon\Crypt\Hash\BCryptHashFunction;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Dto\Account\ClaimStartDto;
use DvsaCommon\Enum\EventTypeCode;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommonApi\Service\AbstractService;
use DvsaEntities\Entity\Address;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\Email;
use DvsaEntities\Entity\EventPersonMap;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\PersonContact;
use DvsaEntities\Entity\PersonSecurityAnswer;
use DvsaEntities\Repository\PersonRepository;
use DvsaEntities\Repository\SecurityQuestionRepository;
use DvsaEventApi\Service\EventService;
use Zend\Authentication\AuthenticationService;

/**
 * Class ClaimService.
 */
class ClaimService extends AbstractService
{
    /** @var EntityManager $entityManager */
    protected $entityManager;
    /** @var AuthenticationService */
    private $motIdentityProvider;
    /** @var ClaimValidator $claimValidator */
    private $claimValidator;
    /** @var SecurityQuestionRepository $securityQuestionRepository */
    private $securityQuestionRepository;
    /** @var PersonRepository $personRepository */
    private $personRepository;
    /** @var OpenAmIdentityService */
    private $openAmIdentityService;
    /** @var EventService $eventService */
    private $eventService;
    /** @var ParamObfuscator */
    private $obfuscator;
    /** @var DateTimeHolder */
    private $dateTimeHolder;
    private $personContactTypeRepository;

    /**
     * @param EntityManager                $entityManager
     * @param MotIdentityProviderInterface $motIdentityProvider
     * @param ClaimValidator               $claimValidator
     * @param SecurityQuestionRepository   $securityQuestionRepository
     * @param PersonRepository             $personRepository
     * @param OpenAmIdentityService        $openAmIdentityService
     * @param EventService                 $eventService
     * @param ParamObfuscator              $obfuscator
     * @param DateTimeHolder               $dateTimeHolder
     * @param EntityRepository             $personContactTypeRepository
     */
    public function __construct(
        EntityManager $entityManager,
        MotIdentityProviderInterface $motIdentityProvider,
        ClaimValidator $claimValidator,
        SecurityQuestionRepository $securityQuestionRepository,
        PersonRepository $personRepository,
        OpenAmIdentityService $openAmIdentityService,
        EventService $eventService,
        ParamObfuscator $obfuscator,
        DateTimeHolder $dateTimeHolder,
        EntityRepository $personContactTypeRepository
    ) {
        parent::__construct($entityManager);

        $this->entityManager = $entityManager;
        $this->motIdentityProvider = $motIdentityProvider;
        $this->claimValidator = $claimValidator;
        $this->securityQuestionRepository = $securityQuestionRepository;
        $this->personRepository = $personRepository;
        $this->openAmIdentityService = $openAmIdentityService;
        $this->eventService = $eventService;
        $this->obfuscator = $obfuscator;
        $this->dateTimeHolder = $dateTimeHolder;
        $this->personContactTypeRepository = $personContactTypeRepository;
    }

    /**
     * Save user details, if the parameters are valid.
     *
     * @param array|null $data
     *
     * @throws \DvsaCommon\Exception\UnauthorisedException
     * @throws \Exception
     *
     * @return bool
     */
    public function save(array $data = null)
    {
        if (is_null($data)) {
            throw new \Exception('No data specified');
        }

        if (!isset($data['personId']) || $data['personId'] != $this->getUserId() || !$this->isAccountClaimRequired()) {
            throw new UnauthorisedException('Invalid Request');
        }

        $person = $this->savePerson($data);

        //  --  raise new event --
        /* Log Account Successfully Created */
        if ($this->eventService->isEventCreatedBy($person, EventTypeCode::USER_CLAIMS_ACCOUNT) === true) {
            $event = $this->eventService->addEvent(
                EventTypeCode::USER_RECLAIMS_ACCOUNT,
                sprintf(EventDescription::USER_RECLAIMS_ACCOUNT, $person->getUsername()),
                $this->dateTimeHolder->getCurrent(true)
            );
        } else {
            $event = $this->eventService->addEvent(
                EventTypeCode::USER_CLAIMS_ACCOUNT,
                sprintf(EventDescription::USER_CLAIMS_ACCOUNT, $person->getUsername()),
                $this->dateTimeHolder->getCurrent(true)
            );
        }

        $eventMap = (new EventPersonMap())
            ->setEvent($event)
            ->setPerson($person);

        $this->entityManager->persist($eventMap);
        $this->entityManager->flush();

        return true;
    }

    /**
     * @param $data
     *
     * @throws \AccountApi\Service\Exception\OpenAmChangePasswordException
     *
     * @return \DvsaEntities\Entity\Person
     */
    private function savePerson($data)
    {
        $this->claimValidator->validate($data);
        $securityQuestions = $this->claimValidator->validateSecurityQuestions($data);

        $person = $this->getPerson();

        /*
         * Change password in OpenAM.
         *
         * The stack trace is removed deliberately to prevent plaintext password logging.
         */
        try {
            $this->openAmIdentityService->changePassword($person->getUsername(), $data['password']);
            $this->openAmIdentityService->unlockAccount($person->getUsername());
        } catch (OpenAmChangePasswordException $e) {
            throw new OpenAmChangePasswordException($e->getMessage());
        }

        $person = $this->saveSecurityQuestions($person, $data, $securityQuestions);
        $person->setAccountClaimRequired(false);
        //prevents situation when user is asked to reset password right after setting new during account claim
        $person->setPasswordChangeRequired(false);

        $this->entityManager->persist($person);

        return $person;
    }

    /**
     * @description Create a new PersonContact object for the person entity only if the email address is specified
     *
     * @param Person $person
     *
     * @return PersonContact
     */
    private function createEmptyPersonContact(Person $person)
    {
        $address = new Address();
        $address->setAddressLine1('');

        $contact = new ContactDetail();
        $contact->setAddress($address);

        $personContactType = $this->personContactTypeRepository->findOneBy(['name' => PersonContactTypeEnum::PERSONAL]);

        $personContact = new PersonContact($contact, $personContactType, $person);

        return $personContact;
    }

    /**
     * Save security questions to person entity.
     *
     * @param Person                                  $person
     * @param array                                   $data
     * @param \DvsaEntities\Entity\SecurityQuestion[] $securityQuestions
     *
     * @return Person
     */
    private function saveSecurityQuestions(Person $person, array $data, array $securityQuestions)
    {
        foreach ($person->getSecurityAnswers() as $answer) {
            $this->entityManager->remove($answer);
        }
        $this->entityManager->flush();

        $hashFunction = new SecurityAnswerHashFunction();

        foreach (['One', 'Two'] as $index) {
            $personSecurityAnswer = new PersonSecurityAnswer(
                $securityQuestions[$data['securityQuestion'.$index.'Id']],
                $person,
                $hashFunction->hash($data['securityAnswer'.$index])
            );

            $person->addSecurityAnswer($personSecurityAnswer);
        }

        return $person;
    }

    /**
     * @return int
     * @description return ID from zend identity
     */
    private function getUserId()
    {
        return $this->motIdentityProvider->getIdentity()->getUserId();
    }

    /**
     * @description we collect this from the entity as the person object is stored in the zend identity.
     *              It does not get updated everytime the person object record is updated.
     *
     * @return bool
     */
    private function isAccountClaimRequired()
    {
        $person = $this->getPerson($this->getUserId());

        return $person->isAccountClaimRequired();
    }

    /**
     * @description this will return the person entity
     *
     * @return Person
     */
    private function getPerson()
    {
        return $this->personRepository->find($this->getUserId());
    }

    /**
     * Generate a PIN number and save the PIN value as a SHA512 hash into the person entity.
     *
     * @return int
     */
    public function generatePin()
    {
        $pin = rand(0, 999999);
        $pin = str_pad($pin, 6, '0', STR_PAD_LEFT);

        $this->updatePin($this->getUserId(), $pin);

        return $pin;
    }

    /**
     * @return ClaimStartDto
     */
    public function generateClaimAccountData()
    {
        $claimData = new ClaimStartDto();
        $claimData->setPin($this->generatePin());

        return $claimData;
    }

    /**
     * Save the PIN value as a SHA512 hash into the person entity. Returns the number of rows affected.
     *
     * @param int    $personId
     * @param string $pin
     *
     * @return int
     */
    private function updatePin($personId, $pin)
    {
        $hashFunction = new BCryptHashFunction();
        $pinHash = $hashFunction->hash($pin);

        $rows = $this
            ->entityManager
            ->createQuery(sprintf('UPDATE %s p SET p.pin = :pin WHERE p.id = :personId',
                $this->personRepository->getClassName()))
            ->setParameters([
                'pin' => $pinHash,
                'personId' => $personId,
            ])
            ->execute();

        $this->entityManager->flush();

        return $rows;
    }
}
