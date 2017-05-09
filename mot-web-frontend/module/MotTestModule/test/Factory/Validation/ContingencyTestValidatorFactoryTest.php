<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModuleTest\Factory;

use Dvsa\Mot\Frontend\MotTestModule\Factory\Validation\ContingencyTestValidatorFactory;
use Dvsa\Mot\Frontend\MotTestModule\Validation\ContingencyTestValidator;
use DvsaCommonTest\TestUtils\ServiceFactoryTestHelper;
use DvsaCommonTest\TestUtils\XMock;
use DvsaFeature\FeatureToggles;

class ContingencyTestValidatorFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryCreatesInstance()
    {
        ServiceFactoryTestHelper::testCreateServiceForSM(
            ContingencyTestValidatorFactory::class,
            ContingencyTestValidator::class,
            [
                'Feature\FeatureToggles' => function () {
                    return XMock::of(FeatureToggles::class);
                },
            ]
        );
    }
}
