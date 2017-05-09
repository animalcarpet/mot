<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */
use Dvsa\Mot\Frontend\PersonModule\Factory\Security\PersonProfileGuardBuilderFactory;
use Dvsa\Mot\Frontend\PersonModule\Factory\View\ContextProviderFactory;
use Dvsa\Mot\Frontend\PersonModule\Factory\View\PersonProfileUrlGeneratorFactory;
use Dvsa\Mot\Frontend\PersonModule\Security\PersonProfileGuardBuilder;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\PersonModule\View\PersonProfileUrlGenerator;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Action\ChangeSecurityQuestionsAction;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Factory\Action\ChangeSecurityQuestionsActionFactory;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Action\ChangeSecurityQuestionOneAction;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Factory\Action\ChangeSecurityQuestionOneActionFactory;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Action\ChangeSecurityQuestionTwoAction;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Factory\Action\ChangeSecurityQuestionTwoActionFactory;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Action\ChangeSecurityQuestionsReviewAction;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Factory\Action\ChangeSecurityQuestionsReviewActionFactory;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Action\ChangeSecurityQuestionsConfirmationAction;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Factory\Action\ChangeSecurityQuestionsConfirmationActionFactory;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\ChangeSecurityQuestionsService;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Factory\Service\ChangeSecurityQuestionsServiceFactory;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\ChangeSecurityQuestionsSessionService;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Factory\Service\ChangeSecurityQuestionsSessionServiceFactory;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\ChangeSecurityQuestionsStepService;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Factory\Service\ChangeSecurityQuestionsStepServiceFactory;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\PasswordValidationService;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Factory\Service\PasswordValidationServiceFactory;

return [
    'factories' => [
        ContextProvider::class => ContextProviderFactory::class,
        PersonProfileGuardBuilder::class => PersonProfileGuardBuilderFactory::class,
        PersonProfileUrlGenerator::class => PersonProfileUrlGeneratorFactory::class,
        ChangeSecurityQuestionsAction::class => ChangeSecurityQuestionsActionFactory::class,
        ChangeSecurityQuestionOneAction::class => ChangeSecurityQuestionOneActionFactory::class,
        ChangeSecurityQuestionTwoAction::class => ChangeSecurityQuestionTwoActionFactory::class,
        ChangeSecurityQuestionsReviewAction::class => ChangeSecurityQuestionsReviewActionFactory::class,
        ChangeSecurityQuestionsConfirmationAction::class => ChangeSecurityQuestionsConfirmationActionFactory::class,
        ChangeSecurityQuestionsService::class => ChangeSecurityQuestionsServiceFactory::class,
        ChangeSecurityQuestionsSessionService::class => ChangeSecurityQuestionsSessionServiceFactory::class,
        ChangeSecurityQuestionsStepService::class => ChangeSecurityQuestionsStepServiceFactory::class,
        PasswordValidationService::class => PasswordValidationServiceFactory::class,
    ],
];
