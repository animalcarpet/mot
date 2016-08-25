<?php

namespace MailerApi\Logic;

use MailerApi\Service\MailerService;
use DvsaCommon\Dto\Mailer\MailerDto;
use MailerApi\Validator\MailerValidator;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * All business logic for sending a link by email to reclaim an account
 *
 * @package MailerApi\Logic
 */
class ClaimAccountReminder extends Reminder
{
    /** @var string Contains recipient email address */
    protected $emailAddress;

    private $is2faActive;

    /**
     * Constructs the logic controller. Here we trigger validation
     * of the request (in the parent) and then do what is required to cause an email
     * to be sent to the requesting user.
     *
     * @param Array $mailerConfig
     * @param Array $helpdeskConfig
     * @param MailerService $mailerService
     * @param MailerDto $dto
     * @param string $emailAddress to send the reminder to
     * @param bool $is2faActive
     */
    public function __construct(
        Array $mailerConfig,
        Array $helpdeskConfig,
        MailerService $mailerService,
        MailerDto $dto,
        $emailAddress,
        $is2faActive
    ) {
        parent::__construct(
            $mailerConfig,
            $helpdeskConfig,
            $mailerService,
            null,
            $dto,
            MailerValidator::TYPE_RECLAIM_ACCOUNT
        );
        $this->emailAddress = $emailAddress;
        $this->is2faActive = $is2faActive;
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
            'claim-account',
            'reset-subject'
        );

        if ($this->is2faActive) {
            //2fa
            $message = $this->renderTemplate(
                $this->dto,
                '2fa-claim-account',
                'reset',
                $data
            );
        } else {
            //non 2fa
            $message = $this->renderTemplate(
                $this->dto,
                'claim-account',
                'reset',
                $data
            );
        }

        return $this->mailService->send(
            $this->emailAddress,
            $subject,
            $message
        );
    }
}
