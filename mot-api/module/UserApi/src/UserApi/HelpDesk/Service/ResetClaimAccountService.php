<?php

namespace UserApi\HelpDesk\Service;

use AccountApi\Service\Exception\OpenAmChangePasswordException;
use AccountApi\Service\OpenAmIdentityService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\EventDescription;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Dto\Mailer\MailerDto;
use DvsaCommon\Enum\EventTypeCode;
use DvsaEntities\Entity\EventPersonMap;
use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\PersonRepository;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEventApi\Service\EventService;
use MailerApi\Logic\ClaimAccountReminder;
use MailerApi\Service\MailerService;

/**
 * Class ResetClaimAccountService
 * @package UserApi\HelpDesk\Service
 */
class ResetClaimAccountService
{
    const ALPHA = 'abcdefghijklmnopqrstuvwxyz';
    const NUMERIC = '0123456789';
    const LENGTH_PASSWORD = 8;

    const EMAIL_NOT_FOUND = 'Email';

    const CFG_MAILER = 'mailer';
    const CFG_HELPDESK = 'helpdesk';

    const CONFIG_KEY_MOT_WEB_FRONTEND_URL = 'mot-web-frontend-url';

    /** @var  PersonRepository */
    private $personRepository;
    /** @var  EntityManager */
    private $entityManager;
    /** @var  MailerService */
    private $mailerService;
    /** @var  OpenAmIdentityService */
    private $openAmService;
    /** @var  EventService */
    private $eventService;
    /** @var  AuthorisationServiceInterface */
    private $authorisationService;
    /** @var  array */
    private $config;
    /** @var DateTimeHolder */
    private $dateTimeHolder;

    /**
     * Constructor of the ResetClaimAccountService
     *
     * @param EntityManager $entityManager
     * @param PersonRepository $personRepository
     * @param MailerService $mailerService
     * @param OpenAmIdentityService $openAmService
     * @param EventService $eventService
     * @param AuthorisationServiceInterface $authorisationService
     * @param $config
     * @param DateTimeHolder $dateTimeHolder
     */
    public function __construct(
        EntityManager $entityManager,
        PersonRepository $personRepository,
        MailerService $mailerService,
        OpenAmIdentityService $openAmService,
        EventService $eventService,
        AuthorisationServiceInterface $authorisationService,
        $config,
        DateTimeHolder $dateTimeHolder
    ) {
        $this->entityManager = $entityManager;
        $this->personRepository = $personRepository;
        $this->mailerService = $mailerService;
        $this->openAmService = $openAmService;
        $this->eventService = $eventService;
        $this->authorisationService = $authorisationService;
        $this->config = $config;
        $this->dateTimeHolder = $dateTimeHolder;
    }

    /**
     * This function is gonna check that the user has an email
     * Reset the password to a random one, reset the account and
     * send an email to the user with the information to follow
     *
     * @param int $userId
     * @param string $helpDesk
     * @return bool
     * @throws NotFoundException
     * @throws \Exception
     */
    public function resetClaimAccount($userId, $helpDesk)
    {
        $this->authorisationService->assertGranted(PermissionInSystem::VIEW_OTHER_USER_PROFILE);

        $person = $this->personRepository->get($userId);
        $emailAddress = $person->getPrimaryEmail();

        if ($emailAddress === null) {
            throw new NotFoundException(self::EMAIL_NOT_FOUND);
        }

        $person->setAccountClaimRequired(true);

        $newPassword = $this->changePassword($person, $this->generatePassword());
        $this->sendCustomEmail($person, $emailAddress, $newPassword);

        $event = $this->eventService->addEvent(
            EventTypeCode::USER_ACCOUNT_RESET,
            sprintf(EventDescription::USER_ACCOUNT_RESET, $person->getUsername(), $helpDesk),
            $this->dateTimeHolder->getCurrent(true)
        );

        $eventMap = (new EventPersonMap())
            ->setEvent($event)
            ->setPerson($person);
        $this->entityManager->persist($eventMap);

        return true;
    }

    /**
     * Generation of an 8 length password with at least:
     * a lowercase
     * a uppercase
     * a number
     *
     * @return string
     */
    private function generatePassword()
    {
        $alpha = self::ALPHA;
        $alpha_upper = strtoupper($alpha);
        $numeric = self::NUMERIC;
        $chars = $alpha . $alpha_upper . $numeric;

        $pw = substr($alpha, mt_rand(0, strlen($alpha)-1), 1);
        $pw .= substr($alpha_upper, mt_rand(0, strlen($alpha_upper)-1), 1);
        $pw .= substr($numeric, mt_rand(0, strlen($numeric)-1), 1);

        $len = strlen($chars);

        for ($i = 3; $i < self::LENGTH_PASSWORD; $i++) {
            $pw .= substr($chars, mt_rand(0, $len-1), 1);
        }

        return str_shuffle($pw);
    }

    /**
     * Call openAm to change the user password to the generated one
     *
     * @param Person $person
     * @param $newPassword
     * @return string
     * @throws OpenAmChangePasswordException
     */
    private function changePassword(Person $person, $newPassword)
    {
        try {
            $this->openAmService->changePassword($person->getUsername(), $newPassword);
            $this->openAmService->unlockAccount($person->getUsername());
        } catch (OpenAmChangePasswordException $e) {
            throw new OpenAmChangePasswordException($e->getMessage());
        }
        return $newPassword;
    }

    /**
     * Send the email to the user with the new password and the information to follow
     *
     * @param Person $person
     * @param $emailAddress
     * @param $newPassword
     * @return bool
     */
    private function sendCustomEmail(Person $person, $emailAddress, $newPassword)
    {
        $mailerDto = new MailerDto();
        $mailerDto->setData(
            [
                'userid' => $person->getId(),
                'user' => $person
            ]
        );

        $passwordReminder = new ClaimAccountReminder(
            $this->config[self::CFG_MAILER],
            $this->config[self::CFG_HELPDESK],
            $this->mailerService,
            $mailerDto,
            $emailAddress,
            $person->getAuthenticationMethod()->isCard()
        );

        return $passwordReminder->send(
            [
                'newPassword' => $newPassword,
            ]
        );
    }
}
