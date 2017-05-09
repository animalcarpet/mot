<?php

namespace MailerApi\Service;

use Zend\View\Resolver\ResolverInterface;
use Zend\View\Resolver\TemplateMapResolver;

/**
 * Not an ideal solution but wanted to put the template resolver into a place that can be reused by logic services that
 * do not inherit from Reminder.
 */
class TemplateResolverService
{
    /**
     * @var ResolverInterface
     */
    private $resolverInterface;

    /**
     * @param ResolverInterface $resolverInterface
     */
    public function __construct(ResolverInterface $resolverInterface)
    {
        $this->resolverInterface = $resolverInterface;
        $this->resolverInterface->attach($this->getMap());
    }

    /**
     * @return ResolverInterface
     */
    public function getResolver()
    {
        return $this->resolverInterface;
    }

    /**
     * @return TemplateMapResolver
     */
    private function getMap()
    {
        $baseDir = __DIR__.'/../../../view/email/';

        return new TemplateMapResolver(
            [
                'username-reminder' => $baseDir.'username-reminder/message.phtml',
                'username-reminder-subject' => $baseDir.'username-reminder/subject.phtml',
                'username-create' => $baseDir.'username-create/message.phtml',
                'username-create-subject' => $baseDir.'username-create/subject.phtml',
                'password-reminder' => $baseDir.'password-reminder/message.phtml',
                'password-reminder-subject' => $baseDir.'password-reminder/subject.phtml',
                'claim-account-reset' => $baseDir.'claim-account-reset/message.phtml',
                'claim-account-reset-subject' => $baseDir.'claim-account-reset/subject.phtml',
            ]
        );
    }
}
