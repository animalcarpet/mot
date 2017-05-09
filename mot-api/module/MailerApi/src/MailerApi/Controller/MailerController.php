<?php

namespace MailerApi\Controller;

use DvsaCommon\Utility\DtoHydrator;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use DvsaCommon\Dto\Mailer\MailerDto;
use DvsaCommon\Utility\ArrayUtils;
use MailerApi\Logic\UsernameReminder;
use MailerApi\Service\MailerService;
use PersonApi\Service\PersonalDetailsService;
use Zend\ServiceManager\ServiceLocatorInterface;

class MailerController extends AbstractDvsaRestfulController
{
    const USERNAME_SUBJECT = 'Username reminder';

    /** @var ServiceLocatorInterface */
    protected $serviceLocator;

    /**
     * Cause a username reminder email to be sent.
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function create($data)
    {
        /** @var MailerDto $dto */
        $dto = DtoHydrator::jsonToDto($data);

        $globalConfig = $this->getServiceLocator()->get('Config');

        $reminder = new UsernameReminder(
            ArrayUtils::tryGet($globalConfig, 'mailer', []),
            ArrayUtils::tryGet($globalConfig, 'helpdesk', []),
            $this->getServiceLocator()->get(MailerService::class),
            $this->getServiceLocator()->get(PersonalDetailsService::class),
            $dto
        );

        if (true === $reminder->send(['reminderLink' => 'TODO!'])) {
            return ApiResponse::jsonOk(['sent' => 'yes']);
        } else {
            return ApiResponse::jsonOk(['sent' => 'inhibited']);
        }
    }
}
