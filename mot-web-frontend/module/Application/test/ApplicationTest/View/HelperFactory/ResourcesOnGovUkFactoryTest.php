<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace ApplicationTest\Factory\Controller;

use Application\View\Helper\ResourcesOnGovUkHelper;
use Application\View\HelperFactory\ResourcesOnGovUkFactory;
use DvsaCommonTest\TestUtils\ServiceFactoryTestHelper;

/**
 * Covers ResourcesOnGovUkFactory.
 */
class ResourcesOnGovUkFactoryTest extends \PHPUnit_Framework_TestCase
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
            ResourcesOnGovUkFactory::class,
            ResourcesOnGovUkHelper::class,
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
                    'resources' => 'In physics, string theory is a theoretical framework in which the point-like particles'.
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
            'resources' => [
                [
                    'name' => 'MOT inspection manual for class 1 and 2 vehicles',
                    'url' => '/documents/manuals/m1i00000001.htm',
                    'help_text' => 'Manual for motor bicycle and side car testing',
                ],
                [
                    'name' => 'MOT inspection manual for class 3, 4, 5, and 7 vehicles',
                    'url' => '/documents/manuals/m4i00000001.htm',
                    'help_text' => 'Manual for testing private passenger and light commercial vehicles',
                ],
                [
                    'name' => 'MOT testing guide',
                    'url' => 'https://www.gov.uk/government/publications/mot-testing-guide',
                    'help_text' => 'Guidance on how the MOT scheme is run',
                ],
                [
                    'name' => 'In service exhaust emission standards for road vehicles: 18th edition',
                    'url' => 'https://www.gov.uk/government/uploads/system/uploads/attachment_data/file/348035/18th-edition-emissions-book-complete.pdf',
                    'help_text' => 'Standards for checking vehicle exhaust emission procedures and limits',
                ],
            ],
        ];
    }
}
