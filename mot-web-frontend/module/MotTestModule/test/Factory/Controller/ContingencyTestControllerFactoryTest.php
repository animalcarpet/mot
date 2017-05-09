<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModuleTest\Factory;

use Dvsa\Mot\Frontend\MotTestModule\Controller\ContingencyTestController;
use Dvsa\Mot\Frontend\MotTestModule\Factory\Controller\ContingencyTestControllerFactory;
use Dvsa\Mot\Frontend\MotTestModule\Validation\ContingencyTestValidator;
use DvsaCommonTest\TestUtils\ServiceFactoryTestHelper;

class ContingencyTestControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryCreatesInstance()
    {
        ServiceFactoryTestHelper::testCreateServiceForCM(
            ContingencyTestControllerFactory::class,
            ContingencyTestController::class, [
                ContingencyTestValidator::class => ContingencyTestValidator::class,
            ]
        );
    }
}
