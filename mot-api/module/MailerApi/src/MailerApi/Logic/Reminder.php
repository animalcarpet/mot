<?php

namespace MailerApi\Logic;

use DvsaCommon\Dto\Mailer\MailerDto;
use MailerApi\Service\MailerService;
use DvsaCommon\Utility\ArrayUtils;
use PersonApi\Service\PersonalDetailsService;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver;

class Reminder
{
    /** Key for extracting the contact number for people to ring for help */
    const CONFIG_KEY_CONTACT_NUMBER = 'phoneNumber';

    /** Key or the descriptive term for the DVSA help-desk */
    const CONFIG_KEY_CONTACT_LABEL = 'name';

    /** What to show in the mail if no external telephone contact was found */
    const ERROR_NO_CONTACT = 'ERROR: NO CONTACT TELEPHONE!';

    /** What to show in the mail if no external contact label was found */
    const ERROR_NO_CONTACT_LABEL = 'ERROR: NO CONTACT LABEL!';

    /** @var MailerDto */
    protected $dto;

    /** The underlying mail validation and transport service */
    protected $mailService;

    /** @var PersonalDetailsService */
    protected $personalDetailsService;

    /** @var array */
    protected $mailerConfig;

    /** @var array */
    protected $helpdeskConfig;

    /**
     * @param $mailerConfig Array   -- mailer specific configuration data
     * @param $helpdeskConfig Array   -- global help-desk configuration data
     * @param $mailerService MailerService
     * @param $personalDetailsService $personalDetailsService
     * @param $dto MailerDto
     * @param $type int
     */
    public function __construct(
        array $mailerConfig,
        array $helpdeskConfig,
        MailerService $mailerService,
        $personalDetailsService,
        $dto,
        $type
    ) {
        $this->dto = $dto;

        $this->personalDetailsService = $personalDetailsService;
        $this->mailService = $mailerService;
        $this->mailerConfig = $mailerConfig;
        $this->helpdeskConfig = $helpdeskConfig;
        $this->mailService->validate($dto, $type);
    }

    /**
     * Get the designated email recipient based on the DTO user-id.
     *
     * @return string
     */
    protected function getRecipient()
    {
        $dtoData = $this->dto->getData();
        /** @var \DvsaEntities\Entity\Person $person */
        $person = $dtoData['user'];

        /** @var \DvsaEntities\Entity\Person $user */
        $personalDetailsObj = $this->personalDetailsService->get($person->getId());
        $recipientEmail = $personalDetailsObj->getEmail();

        return $recipientEmail;
    }

    /**
     * Render a template. We supply the type so the template files know what they
     * are about. This makes it easy to change the outgoing subject and message
     * content without touching the code. (Yes, I know the config file *is* code).
     *
     * @param MailerDto $dto
     * @param string    $type
     * @param string    $templateName
     * @param array     $data         contains extra template specific data.
     *                                ===================================================================
     *                                NOTE: DO NOT USE KEYS "type" "user" or "contactNumber" as they will
     *                                be overwritten.
     *                                ===================================================================
     *
     * @return string
     */
    protected function renderTemplate(MailerDto $dto, $type, $templateName, $data = [])
    {
        $dtoData = $dto->getData();

        /** @var \DvsaEntities\Entity\Person $user */
        $user = $dtoData['user'];

        // Set some data that templates can make use of, NOTE: this may overwrite values
        // passed in. Caller beware!!!!!!!!
        $data['type'] = $type; // Report type
        $data['user'] = $user; // Person instance for the email recipient

        // set the contact telephone number
        $data['contactNumber'] = ArrayUtils::tryGet(
            $this->helpdeskConfig,
            self::CONFIG_KEY_CONTACT_NUMBER,
            self::ERROR_NO_CONTACT
        );

        // set the contact descriptive text
        $data['contactLabel'] = ArrayUtils::tryGet(
            $this->helpdeskConfig,
            self::CONFIG_KEY_CONTACT_LABEL,
            self::ERROR_NO_CONTACT_LABEL
        );

        $viewModel = new ViewModel($data);
        $viewModel->setTemplate($type.'-'.$templateName);

        $renderer = new PhpRenderer();
        $resolver = new Resolver\AggregateResolver();
        $renderer->setResolver($resolver);

        $map = new Resolver\TemplateMapResolver(
            [
                'username-reminder' => __DIR__.'/../../../view/email/username-reminder/message.phtml',
                'username-reminder-subject' => __DIR__.'/../../../view/email/username-reminder/subject.phtml',
                'password-reminder' => __DIR__.'/../../../view/email/password-reminder/message.phtml',
                'password-reminder-subject' => __DIR__.'/../../../view/email/password-reminder/subject.phtml',
                'claim-account-reset' => __DIR__.'/../../../view/email/claim-account-reset/message.phtml',
                'claim-account-reset-subject' => __DIR__.'/../../../view/email/claim-account-reset/subject.phtml',
            ]
        );
        $resolver->attach($map);
        $message = $renderer->render($viewModel);

        return $message;
    }
}
