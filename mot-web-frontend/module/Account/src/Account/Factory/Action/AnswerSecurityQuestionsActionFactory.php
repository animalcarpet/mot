<?php

namespace Account\Factory\Action;

use Account\Action\PasswordReset\AnswerSecurityQuestionsAction;
use Account\Service\SecurityQuestionService;
use DvsaCommon\Configuration\MotConfig;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AnswerSecurityQuestionsActionFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return AnswerSecurityQuestionsAction
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var SecurityQuestionService $service */
        $service = $serviceLocator->get(SecurityQuestionService::class);

        /** @var MotConfig $config */
        $config = $serviceLocator->get(MotConfig::class);

        return new AnswerSecurityQuestionsAction($service, $config->get('helpdesk'));
    }
}
