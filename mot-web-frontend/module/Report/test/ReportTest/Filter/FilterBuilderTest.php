<?php

namespace ReportTest\Filter;

use DvsaCommonTest\Bootstrap;
use Report\Filter\FilterBuilder;
use Zend\Mvc\Router\Http\TreeRouteStack;
use Zend\Mvc\Router\RouteMatch;
use Zend\Stdlib\Parameters;
use Zend\View\Helper\Url;
use Zend\View\Renderer\PhpRenderer;

class FilterBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @var FilterBuilder */
    private $targetClass;
    /** @var PhpRenderer */
    private static $renderer;

    public function setUp()
    {
        $this->targetClass = new FilterBuilder();
    }

    /**
     * @param $options
     * @param $queryParams
     *
     * @dataProvider dataProviderTestGetNav
     */
    public function testGetTimePeriodNavigation($options, $queryParams, $expectedLinks)
    {
        $this->targetClass->setOptions($options);

        $params = new Parameters($queryParams);
        $this->targetClass->setQueryParams($params);

        $this->assertEquals(
            $expectedLinks,
            $this->targetClass->getTimePeriodNavigation($this->getViewRenderer())->count()
        );
    }

    public function dataProviderTestGetNav()
    {
        return [
            [
                'options' => [
                    'today' => [
                        'label' => 'Today',
                        'from' => strtotime('today'),
                        'to' => strtotime('tomorrow -1 second'),
                    ],
                    'lastWeek' => [
                        'label' => 'Last week (Mon-Sun)',
                        'from' => strtotime('last monday - 7 days'),
                        'to' => strtotime('last monday - 1 second'),
                    ],
                    'lastMonth' => [
                        'label' => 'Last Month ('.date('M', strtotime('last month')).')',
                        'from' => strtotime('first day of last month'),
                        'to' => strtotime('last day of last month'),
                    ],
                ],
                'queryParams' => [
                    'dateFrom' => strtotime('today'),
                    'dateTo' => strtotime('tomorrow -1 second'),
                ],
                'expectedLinks' => 3,
            ],
            [
                'options' => [
                    'today' => [
                        'label' => 'Today',
                        'from' => strtotime('today'),
                        'to' => strtotime('tomorrow -1 second'),
                    ],
                ],
                'queryParams' => [],
                'expectedLinks' => 1,
            ],
        ];
    }

    private function getViewRenderer()
    {
        if (self::$renderer) {
            return self::$renderer;
        }

        $appTestConfig = include getcwd().'/test/test.config.php';
        Bootstrap::init($appTestConfig);

        /** @var \Zend\ServiceManager\ServiceManager $serviceManager */
        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager->setAllowOverride(true);

        self::$renderer = new PhpRenderer([]);

        $mockUrl = new Url();
        $mockUrl->setRouter(TreeRouteStack::factory($serviceManager->get('Config')['router']));
        $mockUrl->setRouteMatch((new RouteMatch([]))->setMatchedRouteName('user-home'));

        $viewHelperManager = $serviceManager->get('ViewHelperManager');
        $viewHelperManager->setService('url', $mockUrl);

        return self::$renderer
            ->setHelperPluginManager($viewHelperManager);
    }
}
