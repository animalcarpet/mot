<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

use Dvsa\Mot\Frontend\PersonModule\Controller\ChangeAddressController;
use Dvsa\Mot\Frontend\PersonModule\Controller\ChangeNameController;
use Dvsa\Mot\Frontend\PersonModule\Controller\ChangeTelephoneController;
use Dvsa\Mot\Frontend\PersonModule\Controller\PersonProfileController;
use Dvsa\Mot\Frontend\PersonModule\Factory\Controller\ChangeAddressControllerFactory;
use Dvsa\Mot\Frontend\PersonModule\Factory\Controller\ChangeNameControllerFactory;
use Dvsa\Mot\Frontend\PersonModule\Factory\Controller\ChangeTelephoneControllerFactory;
use Dvsa\Mot\Frontend\PersonModule\Factory\Controller\PersonProfileControllerFactory;
use Dashboard\Controller\UserTradeRolesController;
use Dashboard\Factory\Controller\UserTradeRolesControllerFactory;
use UserAdmin\Controller\EmailAddressController;
use UserAdmin\Factory\Controller\ChangeQualificationStatusControllerFactory;
use UserAdmin\Controller\ChangeQualificationStatusController;
use UserAdmin\Factory\Controller\EmailAddressControllerFactory;
use UserAdmin\Factory\Controller\UserProfileControllerFactory;
use UserAdmin\Controller\UserProfileController;
use Dvsa\Mot\Frontend\PersonModule\Controller\ChangeDateOfBirthController;
use Dvsa\Mot\Frontend\PersonModule\Factory\Controller\ChangeDateOfBirthControllerFactory;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Controller\ChangeSecurityQuestionsController;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Factory\Controller\ChangeSecurityQuestionsControllerFactory;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Controller\ChangeSecurityQuestionOneController;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Factory\Controller\ChangeSecurityQuestionOneControllerFactory;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Controller\ChangeSecurityQuestionTwoController;
use \Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Factory\Controller\ChangeSecurityQuestionTwoControllerFactory;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Controller\ChangeSecurityQuestionsConfirmationController;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Factory\Controller\ChangeSecurityQuestionsConfirmationControllerFactory;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Controller\ChangeSecurityQuestionsReviewController;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Factory\Controller\ChangeSecurityQuestionsReviewControllerFactory;

return [
    'factories' => [
        UserProfileControllerFactory::class => UserProfileController::class,
        PersonProfileController::class    => PersonProfileControllerFactory::class,
        UserTradeRolesController::class   => UserTradeRolesControllerFactory::class,
        ChangeQualificationStatusController::class => ChangeQualificationStatusControllerFactory::class,
        ChangeAddressController::class => ChangeAddressControllerFactory::class,
        ChangeQualificationStatusController::class => ChangeQualificationStatusControllerFactory::class,
        ChangeNameController::class => ChangeNameControllerFactory::class,
        ChangeDateOfBirthController::class => ChangeDateOfBirthControllerFactory::class,
        ChangeTelephoneController::class => ChangeTelephoneControllerFactory::class,
        EmailAddressController::class => EmailAddressControllerFactory::class,
        ChangeSecurityQuestionsController::class => ChangeSecurityQuestionsControllerFactory::class,
        ChangeSecurityQuestionOneController::class => ChangeSecurityQuestionOneControllerFactory::class,
        ChangeSecurityQuestionTwoController::class => ChangeSecurityQuestionTwoControllerFactory::class,
        ChangeSecurityQuestionsReviewController::class => ChangeSecurityQuestionsReviewControllerFactory::class,
        ChangeSecurityQuestionsConfirmationController::class => ChangeSecurityQuestionsConfirmationControllerFactory::class,
    ],
];
