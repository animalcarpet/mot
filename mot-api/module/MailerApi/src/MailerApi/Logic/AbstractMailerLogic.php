<?php

namespace MailerApi\Logic;

use MailerApi\Service\MailerService;
use MailerApi\Service\TemplateResolverService;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Model\ViewModel;

/**
 * @todo Change other logic functions to use this
 */
abstract class AbstractMailerLogic
{
    const CONFIG_KEY = 'mailer';
    const CONFIG_KEY_BASE_URL = 'mot-web-frontend-url';

    /**
     * @var MailerService
     */
    protected $mailerService;

    /**
     * @var TemplateResolverService
     */
    private $templateResolverService;

    /**
     * Configuration variables, passed in via service locator.
     *
     * @var array
     */
    private $mailerConfig;

    /**
     * @param MailerService           $mailerService
     * @param TemplateResolverService $templateResolverService
     * @param array                   $config                  Passed in by serviceLocator
     */
    public function __construct(
        MailerService $mailerService,
        TemplateResolverService $templateResolverService,
        array $config
    ) {
        $this->mailerService = $mailerService;
        $this->templateResolverService = $templateResolverService;
        $this->mailerConfig = $config[self::CONFIG_KEY];
    }

    abstract public function prepareSubject(array $data = []);
    abstract public function prepareMessage(array $data = []);

    /**
     * @param string $recipient
     * @param string $subject
     * @param string $message
     *
     * @return bool
     */
    final public function send($recipient, $subject, $message)
    {
        return $this->mailerService->send(
            $recipient,
            $subject,
            $message
        );
    }

    /**
     * @param string $type
     * @param string $templateName
     * @param array  $data
     *
     * @return string
     */
    public function renderTemplate($type, $templateName, $data = [])
    {
        $resolver = $this->templateResolverService->getResolver();

        $renderer = new PhpRenderer();
        $renderer->setResolver($resolver);

        $viewModel = new ViewModel($data);
        $viewModel->setTemplate($type.'-'.$templateName);

        return $renderer->render($viewModel);
    }

    /**
     * Returns the base URL from the mailer configuration.
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return isset($this->mailerConfig[self::CONFIG_KEY_BASE_URL]) ? $this->mailerConfig[self::CONFIG_KEY_BASE_URL] : null;
    }
}
