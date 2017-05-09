<?php

namespace AccountApi\Service;

use AccountApi\Service\Exception\OpenAmChangePasswordException;
use AccountApi\Service\Mapper\MessageMapper;
use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Dto\Mailer\MailerDto;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Enum\MessageTypeCode;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommon\UrlBuilder\AccountUrlBuilderWeb;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Service\AbstractService;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\Message;
use DvsaEntities\Entity\MessageType;
use DvsaEntities\Entity\Person;
use MailerApi\Logic\AbstractMailerLogic;
use MailerApi\Logic\PasswordReminder;
use MailerApi\Service\MailerService;
use Zend\Authentication\AuthenticationService;
use Zend\Log\LoggerInterface;

/**
 * Class TokenService.
 */
class TokenService extends AbstractService
{
    const MAX_GENERATION_ATTEMPT = 10;
    const ERROR_TOKEN_LOG = 'A unique token could not be generated in %d attempts. Token generation aborted.';
    const ERROR_GENERATION_TOKEN = 'An error has occurred while generating the link';

    const CONFIG_KEY_MOT_WEB_FRONTEND_URL = 'mot-web-frontend-url';
    const EMAIL_NOT_FOUND = 'Email';
    const CFG_PASSWORD_RESET = 'password_reset';
    const CFG_PASSWORD_RESET_SECRET = 'secret';
    const CFG_PASSWORD_RESET_HASH_METHOD = 'hash_method';
    const CFG_PASSWORD_RESET_EXPIRE_TIME = 'expireTime';
    const CFG_MAILER = 'mailer';
    const CFG_HELPDESK = 'helpdesk';

    /** @var \DvsaEntities\Repository\MessageRepository */
    private $messageRepository;
    /** @var \DvsaEntities\Repository\MessageTypeRepository */
    private $messageTypeRepository;
    /** @var \DvsaEntities\Repository\PersonRepository */
    private $personRepository;
    /** @var LoggerInterface $logger */
    private $logger;

    /** \MailerApi\Service\MailerService */
    private $mailerService;
    /** @var OpenAmIdentityService */
    protected $openAmIdentityService;

    /** @var array */
    private $mailerConfig;
    /** @var array */
    private $helpdeskConfig;

    /** @var string */
    private $secret;
    /** @var string */
    private $encoding;
    /** @var float */
    private $expireTime;

    /** @var DateTimeHolder */
    protected $dateTimeHolder;

    /** @var MessageMapper */
    protected $messageMapper;

    /** @var ParamObfuscator */
    protected $obfuscator;

    /** @var AuthorisationServiceInterface */
    protected $authService;

    /** @var AuthenticationService */
    protected $identityService;

    /**
     * @param EntityManager                                  $entityManager
     * @param LoggerInterface                                $logger
     * @param array                                          $config
     * @param \DvsaEntities\Repository\MessageRepository     $messageRepository
     * @param \DvsaEntities\Repository\MessageTypeRepository $messageTypeRepository
     * @param \DvsaEntities\Repository\PersonRepository      $personRepository
     * @param MailerService                                  $mailerService
     * @param OpenAmIdentityService                          $openAmIdentityService
     */
    public function __construct(
        EntityManager $entityManager,
        $messageRepository,
        $messageTypeRepository,
        $personRepository,
        LoggerInterface $logger,
        MailerService $mailerService,
        OpenAmIdentityService $openAmIdentityService,
        array $config,
        ParamObfuscator $obfuscator,
        AuthenticationService $identityService,
        AuthorisationServiceInterface $authService
    ) {
        parent::__construct($entityManager);

        //  -- OpenAM --
        $this->openAmIdentityService = $openAmIdentityService;

        //  --  Mailer Service  --
        $this->mailerService = $mailerService;
        $this->mailerConfig = $config[AbstractMailerLogic::CONFIG_KEY];
        $this->helpdeskConfig = $config[self::CFG_HELPDESK];

        //  --  config  --
        $resetPassCfg = $config[self::CFG_PASSWORD_RESET];

        $this->secret = $resetPassCfg[self::CFG_PASSWORD_RESET_SECRET];
        $this->encoding = $resetPassCfg[self::CFG_PASSWORD_RESET_HASH_METHOD];
        $this->expireTime = $resetPassCfg[self::CFG_PASSWORD_RESET_EXPIRE_TIME];

        //  --  repositories    --
        $this->messageRepository = $messageRepository;
        $this->messageTypeRepository = $messageTypeRepository;
        $this->personRepository = $personRepository;

        //  --
        $this->logger = $logger;
        $this->dateTimeHolder = new DateTimeHolder();
        $this->messageMapper = new MessageMapper();
        $this->obfuscator = $obfuscator;

        $this->authService = $authService;
        $this->identityService = $identityService;
    }

    /**
     * This function create a token to store in the database and
     * send an email to the user to reset his password.
     *
     * @param string $userId
     *
     * @return array
     *
     * @throws NotFoundException
     * @throws \Exception
     */
    public function createTokenAndEmailForgottenLink($userId)
    {
        extract($this->createToken($userId));
        $this->sendCustomEmail($person, $tokenData['token'], $person->getPrimaryEmail());

        return $this->messageMapper->toDto($message);
    }

    /**
     * Creates a unique token and inserts the relevant message against the user supplied.
     *
     * @param mixed $userId or $username
     *
     * @return array
     *
     * @throws NotFoundException
     * @throws \Exception
     */
    private function createToken($userIdOrUsername)
    {
        /** @var Person $person */
        $person = $this->personRepository->getByIdOrUsername($userIdOrUsername);

        /** @var MessageType $messageType */
        $messageType = $this->messageTypeRepository->getByCode(MessageTypeCode::PASSWORD_RESET_BY_EMAIL);

        $this->checkIfTokenIsAlreadyGenerate($person);
        $tokenData = $this->createUniqueToken($person->getUsername());

        $message = (new Message())
            ->setPerson($person)
            ->setMessageType($messageType)
            ->setIssueDate((new \DateTime())->setTimestamp($tokenData['issued']))
            ->setExpiryDate((new \DateTime())->setTimestamp($tokenData['expiry']))
            ->setToken($tokenData['token']);

        $this->setUserIdIfConnectionEstablished($this->entityManager);

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        return [
            'person' => $person,
            'tokenData' => $tokenData,
            'message' => $message,
        ];
    }

    /**
     * This function will check if a token has been already generate but not use and make it unusable if there's one.
     *
     * @param Person $person
     */
    private function checkIfTokenIsAlreadyGenerate(Person $person)
    {
        /** @var Message[] $oldTokens */
        $oldTokens = $this->messageRepository->findBy(['person' => $person, 'isAcknowledged' => false]);
        foreach ($oldTokens as $oldToken) {
            $oldToken->setIsAcknowledged(true);
            $this->entityManager->persist($oldToken);
        }
    }

    /**
     * This function generate a Token for the user to reset his password
     * We check that the token does not already exist in the db and iterate until we have a unique one.
     *
     * We then return the array use to generate the token
     *
     * @param string $userName
     * @param int    $iteration
     *
     * @return array
     *
     * @throws \Exception
     */
    private function createUniqueToken($userName, $iteration = 0)
    {
        if ($iteration >= self::MAX_GENERATION_ATTEMPT) {
            $this->logger->debug(sprintf(self::ERROR_TOKEN_LOG, self::MAX_GENERATION_ATTEMPT));
            throw new \Exception(self::ERROR_GENERATION_TOKEN);
        }

        $issuedDate = $this->dateTimeHolder->getTimestamp(true);
        $expiryDate = $issuedDate + (float) $this->expireTime;
        $token = $this->defineToken([$issuedDate, $expiryDate, $userName]);

        try {
            $this->messageRepository->getHydratedMessageByToken($token);

            //  --  if token found, then try to regenerate token    --
            return $this->createUniqueToken($userName, $iteration + 1);
        } catch (NotFoundException $e) {
            //  token not found, it is positive exit, so no need throw anything
        }

        return [
            'issued' => $issuedDate,
            'expiry' => $expiryDate,
            'token' => $token,
        ];
    }

    /**
     * Create the unique token for the password reset link.
     *
     * @param array $data
     *
     * @return string
     */
    protected function defineToken(array $data)
    {
        $secret = hash($this->encoding, $this->secret);

        return hash_hmac($this->encoding, implode('|', $data), $secret);
    }

    /**
     * This will creates the formatted email and cause it to be sent to
     * the email address of the recipient.
     *
     * @param Person $person       contains the target user for the reminder
     * @param string $token        the reset token to put in the mail
     * @param string $emailAddress the recipient address for the reminder
     *
     * @return bool TRUE of the mail was forwarded successfully
     */
    protected function sendCustomEmail(Person $person, $token, $emailAddress)
    {
        // Create a DTO
        $mailerDto = new MailerDto();
        $mailerDto->setData(
            [
                'userid' => $person->getId(),
                'user' => $person,
            ]
        );

        // Kick of PasswordReminder
        $passwordReminder = new PasswordReminder(
            $this->mailerConfig,
            $this->helpdeskConfig,
            $this->mailerService,
            $mailerDto,
            $emailAddress
        );

        return $passwordReminder->send(
            [
                'reminderLink' => $this->generateResetLink($token),
            ]
        );
    }

    /**
     * Get the address to the front end from the configuration
     * and add the path to the reset password url.
     *
     * @param $token
     *
     * @return string
     */
    protected function generateResetLink($token)
    {
        $appUrl = ArrayUtils::tryGet(
            $this->mailerConfig,
            AbstractMailerLogic::CONFIG_KEY_BASE_URL
        );

        return $appUrl.AccountUrlBuilderWeb::resetPasswordByToken($token);
    }

    /**
     * This function validate if the token use by the user is valid.
     *
     * @param string $token
     *
     * @return \DvsaCommon\Dto\Account\MessageDto
     *
     * @throws NotFoundException
     */
    public function getToken($token, $onlyValid = false)
    {
        $message = $this->messageRepository->getHydratedMessageByToken($token, $onlyValid);

        return $this->messageMapper->toDto($message);
    }

    /**
     * This function validate if the token use by the user is valid.
     *
     * @param string $token
     *
     * @return int
     *
     * @throws NotFoundException
     */
    public function assertTokenIsValid($token)
    {
        $result = $this->messageRepository->getHydratedMessageByToken($token, true);

        return $result instanceof Message;
    }

    /**
     * Acknowledge the token.
     *
     * @param string $token
     *
     * @throws NotFoundException
     */
    public function acknowledge($token)
    {
        $message = $this->messageRepository->getHydratedMessageByToken($token);
        $message->setIsAcknowledged(true);
        $this->setUserIdIfConnectionEstablished($this->entityManager);
        $this->entityManager->persist($message);
        $this->entityManager->flush($message);
    }

    /**
     * Call openAm to change the user password to the new one.
     *
     * @param $token
     * @param $newPassword
     *
     * @return array
     *
     * @throws OpenAmChangePasswordException
     */
    public function changePassword($token, $newPassword)
    {
        // validate token and return token dto
        // @throws NotFoundException
        $tokenDto = $this->getToken($token, true);
        $username = $tokenDto->getPerson()->getUsername();

        try {
            $this->openAmIdentityService->changePassword($username, $newPassword);
            /*
             * @VM-9838
             *
             * If a user locks his account we need to unlock it when resetting the password or otherwise he won't be
             * able to login with the new password.
             *
             * Ideally we would hook the unlock procedure into a reset password endpoint but for now this seems to be
             * the best (and only) place to set it.
             */
            $this->openAmIdentityService->unlockAccount($username);
            $this->acknowledge($token);
        } catch (OpenAmChangePasswordException $e) {
            throw new OpenAmChangePasswordException($e->getMessage());
        }

        return ['success' => true];
    }

    /**
     * Call openAm to change the user password to the new one
     * Does not require a pre-existing token.
     *
     * @param $userId
     * @param $newPassword
     *
     * @return array
     *
     * @throws OpenAmChangePasswordException
     * @throws NotFoundException
     */
    public function updatePassword($userId, $newPassword)
    {
        $this->assertAuthenticatedMatchesId($userId);
        try {
            $person = $this->personRepository->find($userId);
            if (!$person) {
                throw new NotFoundException(Person::class);
            }

            $username = $person->getUsername();
            $this->openAmIdentityService->changePassword($username, $this->obfuscator->deobfuscate($newPassword));
            /*
             * @VM-9838
             *
             * If a user locks his account we need to unlock it when resetting the password or otherwise he won't be
             * able to login with the new password.
             *
             * Ideally we would hook the unlock procedure into a reset password endpoint but for now this seems to be
             * the best (and only) place to set it.
             */
            $this->openAmIdentityService->unlockAccount($username);
            $this->changePasswordFlagUnset($person);
        } catch (OpenAmChangePasswordException $e) {
            throw new OpenAmChangePasswordException($e->getMessage());
        }

        return ['success' => true];
    }

    private function changePasswordFlagUnset(Person $person)
    {
        $person->setPasswordChangeRequired(false);
        $this->personRepository->save($person);
    }

    private function setUserIdIfConnectionEstablished(EntityManager $entityManager)
    {
        $connection = $entityManager->getConnection();

        if ($connection !== null) {
            $connection->exec("SET @app_user_id = (SELECT `id` FROM `person` WHERE `user_reference` = 'Static Data' OR `username` = 'static data' LIMIT 1)");
        }
    }

    /**
     * Performs assertion based on whether the user has authenticated and whether they are authenticated
     * as the supplied user id.
     *
     * @param $id
     *
     * @throws UnauthorisedException
     */
    private function assertAuthenticatedMatchesId($id)
    {
        if (!$this->identityService->getIdentity()
            || $this->identityService->getIdentity()->getUserId() !== intval($id)) {
            throw new UnauthorisedException('You are not authorised to access this resource');
        }
    }
}
