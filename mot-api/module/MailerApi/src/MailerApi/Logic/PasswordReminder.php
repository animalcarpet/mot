<?php

namespace MailerApi\Logic;

use MailerApi\Service\MailerService;
use DvsaCommon\Dto\Mailer\MailerDto;
use MailerApi\Validator\MailerValidator;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * All business logic for sending a password reminder is inside here.
 *
 * @package MailerApi\Logic
 */
class PasswordReminder extends Reminder
{
    /** @var string Contains recipient email address */
    protected $emailAddress;

    /**
     * Constructs the user name reminder logic controller. Here we trigger validation
     * of the request (in the parent) and then do what is required to cause an email
     * to be sent to the requesting user.
     *
     * @param Array $mailerConfig
     * @param Array $helpdeskConfig
     * @param MailerService $mailerService
     * @param MailerDto $dto
     * @param string $emailAddress to send the reminder to
     */
    public function __construct(
        Array $mailerConfig,
        Array $helpdeskConfig,
        MailerService $mailerService,
        MailerDto $dto,
        $emailAddress
    ) {
        parent::__construct(
            $mailerConfig,
            $helpdeskConfig,
            $mailerService,
            null,
            $dto,
            MailerValidator::TYPE_REMIND_PASSWORD
        );
        $this->emailAddress = $emailAddress;
    }

    /**
     * Attempts to deliver a message to a mail subsystem.
     *
     * @param $data
     *
     * @return bool TRUE of the message was accepted by the mail sub-system.
     *              This does NOT mean the message has been delivered yet.
     */
    public function send(Array $data = [])
    {
        $subject = $this->renderTemplate(
            $this->dto,
            'password',
            'reminder-subject'
        );

        $message = $this->renderTemplate(
            $this->dto,
            'password',
            'reminder',
            $data
        );

        return $this->mailService->send(
            $this->emailAddress,
            $subject,
            $message
        );
    }
}
