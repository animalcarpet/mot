<?php

namespace MailerApi\Logic;

use DvsaCommon\Dto\Mailer\MailerDto;
use MailerApi\Service\MailerService;
use MailerApi\Validator\MailerValidator;
use PersonApi\Service\PersonalDetailsService;

/**
 * Class UsernameReminder.
 *
 * All business logic for sending a username reminder is inside here.
 */
class UsernameReminder extends Reminder
{
    /**
     * Constructs the user name reminder logic controller. Here we trigger validation
     * of the request (in the parent) and then do what is required to cause an email
     * to be sent to the requesting user.
     *
     * @param array $mailerConfig
     * @param array $helpdeskConfig
     * @param $mailerService MailerService
     * @param $personalDetailsService $personalDetailsService
     * @param $dto MailerDto
     */
    public function __construct(
        array $mailerConfig,
        array $helpdeskConfig,
        MailerService $mailerService,
        PersonalDetailsService $personalDetailsService,
        MailerDto $dto
    ) {
        parent::__construct(
            $mailerConfig,
            $helpdeskConfig,
            $mailerService,
            $personalDetailsService,
            $dto,
            MailerValidator::TYPE_REMIND_USERNAME
        );
    }

    /**
     * Attempts to deliver a message to a mail subsystem.
     *
     * @param array $data Contains data for template expansion
     *
     * @return bool TRUE of the message was accepted by the mail sub-system.
     *              This does NOT mean the message has been delivered yet
     */
    public function send(array $data = [])
    {
        $subject = $this->renderTemplate(
            $this->dto,
            'username',
            'reminder-subject'
        );

        $message = $this->renderTemplate(
            $this->dto,
            'username',
            'reminder',
            $data
        );

        return $this->mailService->send(
            $this->getRecipient($this->dto),
            $subject,
            $message
        );
    }
}
