<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace ApplicationTest\Factory\Controller;

use Application\View\Helper\ManualsAndGuidesHelper;
use Application\View\HelperFactory\ManualsAndGuidesFactory;
use DvsaCommonTest\TestUtils\ServiceFactoryTestHelper;

/**
 * Covers ManualsAndGuidesFactory.
 */
class ManualsAndGuidesFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array  $config
     * @param string $expectedException
     *
     * @dataProvider configProvider
     */
    public function testFactoryCreatesInstance(array $config, $expectedException)
    {
        if (null !== $expectedException) {
            $this->setExpectedException($expectedException);
        }

        ServiceFactoryTestHelper::testCreateServiceForCM(
            ManualsAndGuidesFactory::class,
            ManualsAndGuidesHelper::class,
            [
                'Config' => function () use ($config) {
                    return $config;
                },
            ]
        );
    }

    /**
     * @return array
     */
    public function configProvider()
    {
        return [
            [
                [],
                'OutOfBoundsException',
            ],
            [
                [
                    'manuals' => 'In physics, string theory is a theoretical framework in which the point-like particles'.
                    ' of particle physics are replaced by one-dimensional objects called strings. String theory'.
                    ' describes how these strings propagate through space and interact with each other.',
                ],
                'InvalidArgumentException',
            ],
            [
                $this->getFullConfig(),
                null,
            ],
        ];
    }

    /**
     * @return array
     */
    private function getFullConfig()
    {
        return [
            'manuals' => [
                [
                    'name' => 'MOT inspection manual for class 1 and 2 vehicles',
                    'url' => '/documents/manuals/m1i00000001.htm',
                ],
                [
                    'name' => 'MOT inspection manual for class 3, 4, 5, and 7 vehicles',
                    'url' => '/documents/manuals/m4i00000001.htm',
                ],
                [
                    'name' => 'MOT testing guide',
                    'url' => 'https://www.gov.uk/government/publications/mot-testing-guide',
                ],
                [
                    'name' => 'In service exhaust emission standards for road vehicles: 18th edition',
                    'url' => 'https://www.gov.uk/government/uploads/system/uploads/attachment_data/file/348035/18th-edition-emissions-book-complete.pdf',
                ],
            ],
        ];
    }
}
