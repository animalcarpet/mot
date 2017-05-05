<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://github.com/dvsa/mot
 */

use AccountApi\Controller\ClaimController;
use AccountApi\Controller\SecurityQuestionController;
use AccountApi\Factory\Controller\PasswordChangeControllerFactory;
use AccountApi\Factory\Controller\PasswordResetControllerFactory;
use AccountApi\Factory\Controller\PasswordUpdateControllerFactory;
use AccountApi\Factory\Controller\SecurityQuestionControllerFactory;
use AccountApi\Factory\Controller\ValidateUsernameControllerFactory;


return [
    'invokables' => [
        ClaimController::class => ClaimController::class
    ],
    'factories' => [
        PasswordResetControllerFactory::class => PasswordResetControllerFactory::class,
        PasswordResetControllerFactory::class  => PasswordResetControllerFactory::class,
        PasswordChangeControllerFactory::class => PasswordChangeControllerFactory::class,
        PasswordUpdateControllerFactory::class => PasswordUpdateControllerFactory::class,
        PasswordResetControllerFactory::class => PasswordResetControllerFactory::class,
        ValidateUsernameControllerFactory::class => ValidateUsernameControllerFactory::class,
        SecurityQuestionController::class => SecurityQuestionControllerFactory::class,
    ]
];
