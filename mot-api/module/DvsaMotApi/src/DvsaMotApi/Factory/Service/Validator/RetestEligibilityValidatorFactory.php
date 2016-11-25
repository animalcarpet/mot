<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot
 */

namespace DvsaMotApi\Factory\Service\Validator;

use DvsaEntities\Repository\MotTestRepository;
use DvsaMotApi\Helper\MysteryShopperHelper;
use DvsaMotApi\Service\Validator\RetestEligibility\RetestEligibilityValidator;
use NonWorkingDaysApi\NonWorkingDaysHelper;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RetestEligibilityValidatorFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var NonWorkingDaysHelper $nonWorkingDaysHelper */
        $nonWorkingDaysHelper = $serviceLocator->get('NonWorkingDaysHelper');

        /** @var MotTestRepository $motTestRepository */
        $motTestRepository = $serviceLocator->get(MotTestRepository::class);

        /** @var MysteryShopperHelper $mysteryShopperHelper */
        $mysteryShopperHelper = $serviceLocator->get(MysteryShopperHelper::class);

        return new RetestEligibilityValidator($nonWorkingDaysHelper, $motTestRepository, $mysteryShopperHelper);
    }
}
