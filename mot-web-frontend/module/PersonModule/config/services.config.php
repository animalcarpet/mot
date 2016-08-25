<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

use Dvsa\Mot\Frontend\PersonModule\Factory\Service\SecurityCardServiceFactory;
use Dvsa\Mot\Frontend\PersonModule\Factory\Security\PersonProfileGuardBuilderFactory;
use Dvsa\Mot\Frontend\PersonModule\Factory\View\ContextProviderFactory;
use Dvsa\Mot\Frontend\PersonModule\Factory\View\PersonProfileUrlGeneratorFactory;
use Dvsa\Mot\Frontend\PersonModule\Security\PersonProfileGuardBuilder;
use Dvsa\Mot\Frontend\PersonModule\Service\SecurityCardService;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\PersonModule\View\PersonProfileUrlGenerator;

return [
    'factories' => [
        ContextProvider::class => ContextProviderFactory::class,
        PersonProfileGuardBuilder::class => PersonProfileGuardBuilderFactory::class,
        PersonProfileUrlGenerator::class => PersonProfileUrlGeneratorFactory::class,
    ],
];
