<?php

namespace Dvsa\Mot\Frontend\MotTestModuleTest\View;

use Dvsa\Mot\Frontend\MotTestModule\Exception\RouteNotAllowedInContextException;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyContextProvider;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyUrlGenerator;
use PHPUnit_Framework_TestCase;
use Zend\Http\PhpEnvironment\Request;
use Zend\Mvc\Router\Http\TreeRouteStack;
use Zend\Stdlib\Parameters;

class DefectsJourneyUrlGeneratorTest extends PHPUnit_Framework_TestCase
{
    const MOT_TEST_RESULT_URI = '/mot-test/123456789000';
    const BASE_URI = '/mot-test/123456789000/defects';
    const BROWSE_DEFECT_CATEGORIES_URI = '/mot-test/123456789000/defects/categories';
    const BROWSE_DEFECT_CATEGORY_URI = '/mot-test/123456789000/defects/categories/123';
    const SEARCH_DEFECT_URI = '/mot-test/123456789000/defects/search';
    const DEFECT_ID = '111';
    const CATEGORY_ID = '123';
    const SEARCH_QUERY = '?q=testSearch&p=10';
    /**
     * @var Request
     */
    private $request;

    /**
     * @var TreeRouteStack
     */
    private $router;

    public function setUp()
    {
        $this->router = new TreeRouteStack();
        $this->router->setRoutes(require __DIR__.'/Fixtures/routes_for_defects_url_generator.php');
        $this->request = new Request();
    }

    /**
     * happy path data for addDefect.
     *
     * @return array
     */
    public function toAddDefectProvider()
    {
        return [
            // browse single category context
            [self::DEFECT_ID, 'advisory', self::BROWSE_DEFECT_CATEGORY_URI,  self::BROWSE_DEFECT_CATEGORY_URI.'/add/111/advisory'],
            [self::DEFECT_ID, 'advisory', self::BROWSE_DEFECT_CATEGORY_URI,  self::BROWSE_DEFECT_CATEGORY_URI.'/add/111/advisory'],
            [self::DEFECT_ID, 'prs',      self::BROWSE_DEFECT_CATEGORY_URI,  self::BROWSE_DEFECT_CATEGORY_URI.'/add/111/prs'],
            [self::DEFECT_ID, 'failure',  self::BROWSE_DEFECT_CATEGORY_URI,  self::BROWSE_DEFECT_CATEGORY_URI.'/add/111/failure'],
            // search context
            [self::DEFECT_ID, 'advisory', self::SEARCH_DEFECT_URI,           self::SEARCH_DEFECT_URI.'/add/111/advisory'],
            [self::DEFECT_ID, 'prs',      self::SEARCH_DEFECT_URI,           self::SEARCH_DEFECT_URI.'/add/111/prs'],
            [self::DEFECT_ID, 'failure',  self::SEARCH_DEFECT_URI,           self::SEARCH_DEFECT_URI.'/add/111/failure'],
            // search query passing
            [self::DEFECT_ID, 'failure',  self::SEARCH_DEFECT_URI.self::SEARCH_QUERY, self::SEARCH_DEFECT_URI.'/add/111/failure'.self::SEARCH_QUERY, true],
        ];
    }

    /**
     * @param $defectId
     * @param $defectType
     * @param $currentUrl
     * @param $expectedUrl
     * @param bool $addQueryString
     *
     * @throws RouteNotAllowedInContextException
     * @dataProvider toAddDefectProvider
     */
    public function testToAddDefect($defectId, $defectType, $currentUrl, $expectedUrl, $addQueryString = false)
    {
        $defectsJourneyUrlGenerator = $this->createUrlGenerator($currentUrl, $addQueryString);

        $this->assertEquals($expectedUrl, $defectsJourneyUrlGenerator->toAddDefect($defectId, $defectType));
    }

    /**
     * @return array
     */
    public function toAddDefectInWrongContextProvider()
    {
        return [
            // no context
            [self::DEFECT_ID, 'advisory', ''],
            [self::DEFECT_ID, 'advisory', '/unknownUri'],
            // mot test result context
            [self::DEFECT_ID, 'advisory', self::MOT_TEST_RESULT_URI],
            [self::DEFECT_ID, 'advisory', self::MOT_TEST_RESULT_URI.'/unknownUri'],
            [self::DEFECT_ID, 'advisory', self::MOT_TEST_RESULT_URI.'/add/123'],
            // browse categories (root/primary view)
            [self::DEFECT_ID, 'advisory', self::BROWSE_DEFECT_CATEGORIES_URI],
            [self::DEFECT_ID, 'advisory', self::BROWSE_DEFECT_CATEGORIES_URI.'/unknownUri'],
            [self::DEFECT_ID, 'advisory', self::BROWSE_DEFECT_CATEGORIES_URI.'/add/123'],
        ];
    }

    /**
     * @param $defectId
     * @param $defectType
     * @param $currentUrl
     * @param bool $addQueryString
     *
     * @throws RouteNotAllowedInContextException
     * @dataProvider toAddDefectInWrongContextProvider
     */
    public function testToAddDefectInWrongContext_shouldThrowException($defectId, $defectType, $currentUrl, $addQueryString = false)
    {
        $defectsJourneyUrlGenerator = $this->createUrlGenerator($currentUrl, $addQueryString);

        $this->setExpectedException(RouteNotAllowedInContextException::class);
        $defectsJourneyUrlGenerator->toAddDefect($defectId, $defectType);
    }

    public function toAddManualAdvisoryProvider()
    {
        return [
            [self::SEARCH_DEFECT_URI, self:: SEARCH_DEFECT_URI.'/add/0/advisory'],
            [self::SEARCH_DEFECT_URI.self::SEARCH_QUERY,  self:: SEARCH_DEFECT_URI.'/add/0/advisory'.self::SEARCH_QUERY, true],
            [self::BROWSE_DEFECT_CATEGORIES_URI, self::BROWSE_DEFECT_CATEGORIES_URI.'/add/0/advisory'],
            [self::BROWSE_DEFECT_CATEGORY_URI, self::BROWSE_DEFECT_CATEGORY_URI.'/add/0/advisory'],
        ];
    }

    /**
     * @param $currentUrl
     * @param $expectedUrl
     * @param bool $addQueryString
     *
     * @throws RouteNotAllowedInContextException
     * @dataProvider toAddManualAdvisoryProvider
     */
    public function testToAddManualAdvisory($currentUrl, $expectedUrl, $addQueryString = false)
    {
        $defectsJourneyUrlGenerator = $this->createUrlGenerator($currentUrl, $addQueryString);

        $this->assertEquals($expectedUrl, $defectsJourneyUrlGenerator->toAddManualAdvisory());
    }

    public function toAddManualAdvisoryInWrongContextProvider()
    {
        return [
            [''],
            ['unknownURI'],
            ['unknownURI/other'],
            [self::MOT_TEST_RESULT_URI],
        ];
    }
    /**
     * @param $currentUrl
     *
     * @throws RouteNotAllowedInContextException
     *
     * @dataProvider toAddManualAdvisoryInWrongContextProvider
     */
    public function testToAddManualAdvisorInWrongContext_ShouldThrowException($currentUrl, $addQueryString = false)
    {
        $defectsJourneyUrlGenerator = $this->createUrlGenerator($currentUrl, $addQueryString);

        $this->setExpectedException(RouteNotAllowedInContextException::class);
        $defectsJourneyUrlGenerator->toAddManualAdvisory();
    }

    /**
     * @return array
     */
    public function testToEditDefectProvider()
    {
        return [
            [self::DEFECT_ID, self::MOT_TEST_RESULT_URI, self::BASE_URI.'/111/edit'],
            [self::DEFECT_ID, self::MOT_TEST_RESULT_URI.self::SEARCH_QUERY, self::BASE_URI.'/111/edit'.self::SEARCH_QUERY, true],
            [self::DEFECT_ID, self::BROWSE_DEFECT_CATEGORIES_URI, self::BROWSE_DEFECT_CATEGORIES_URI.'/111/edit'],
            [self::DEFECT_ID, self::BROWSE_DEFECT_CATEGORY_URI, self::BROWSE_DEFECT_CATEGORY_URI.'/111/edit'],
            [self::DEFECT_ID, self::SEARCH_DEFECT_URI, self::SEARCH_DEFECT_URI.'/111/edit'],
        ];
    }

    /**
     * @param $defectId
     * @param $currentUrl
     * @param $expectedUrl
     * @param bool $addQueryString
     *
     * @throws RouteNotAllowedInContextException
     * @dataProvider testToEditDefectProvider
     */
    public function testToEditDefect($defectId, $currentUrl, $expectedUrl, $addQueryString = false)
    {
        $defectsJourneyUrlGenerator = $this->createUrlGenerator($currentUrl, $addQueryString);

        $this->assertEquals($expectedUrl, $defectsJourneyUrlGenerator->toEditDefect($defectId));
    }

    /**
     * @return array
     */
    public function testToEditDefectWithNoContextProvider()
    {
        return [
            [self::DEFECT_ID, ''],
            [self::DEFECT_ID, 'unknownURI'],
            [self::DEFECT_ID, 'unknownURI/other'],
        ];
    }

    /**
     * @param $defectId
     * @param $currentUrl
     * @param bool $addQueryString
     *
     * @throws RouteNotAllowedInContextException
     * @dataProvider testToEditDefectWithNoContextProvider
     */
    public function testToEditDefectWithNoContext_shouldThrow($defectId, $currentUrl, $addQueryString = false)
    {
        $defectsJourneyUrlGenerator = $this->createUrlGenerator($currentUrl, $addQueryString);

        $this->setExpectedException(RouteNotAllowedInContextException::class);
        $defectsJourneyUrlGenerator->toEditDefect($defectId);
    }

    /**
     * @return array
     */
    public function testToRemoveDefectProvider()
    {
        return [
            [self::DEFECT_ID, self::MOT_TEST_RESULT_URI, self::BASE_URI.'/111/remove'],
            [self::DEFECT_ID, self::BROWSE_DEFECT_CATEGORIES_URI, self::BROWSE_DEFECT_CATEGORIES_URI.'/111/remove'],
            [self::DEFECT_ID, self::BROWSE_DEFECT_CATEGORY_URI, self::BROWSE_DEFECT_CATEGORY_URI.'/111/remove'],
            [self::DEFECT_ID, self::SEARCH_DEFECT_URI, self::SEARCH_DEFECT_URI.'/111/remove'],

            [self::DEFECT_ID, self::SEARCH_DEFECT_URI.self::SEARCH_QUERY, self::SEARCH_DEFECT_URI.'/111/remove'.self::SEARCH_QUERY, true],
        ];
    }

    /**
     * @param $identifiedDefectId
     * @param $currentUrl
     * @param $expectedUrl
     *
     * @throws RouteNotAllowedInContextException
     *
     * @dataProvider testToRemoveDefectProvider
     */
    public function testToRemoveDefect($identifiedDefectId, $currentUrl, $expectedUrl, $addQueryString = false)
    {
        $defectsJourneyUrlGenerator = $this->createUrlGenerator($currentUrl, $addQueryString);

        $this->assertEquals($expectedUrl, $defectsJourneyUrlGenerator->toRemoveDefect($identifiedDefectId));
    }

    /**
     * @return array
     */
    public function testToRemoveDefectWithNoContextProvider()
    {
        return [
            [self::DEFECT_ID, ''],
            [self::DEFECT_ID, 'unknownURI'],
            [self::DEFECT_ID, 'unknownURI/other'],
        ];
    }

    /**
     * @param $identifiedDefectId
     * @param $currentUrl
     * @param bool $addQueryString
     *
     * @throws RouteNotAllowedInContextException
     * @dataProvider testToRemoveDefectWithNoContextProvider
     */
    public function testToRemoveDefectWithNoContext($identifiedDefectId, $currentUrl, $addQueryString = false)
    {
        $defectsJourneyUrlGenerator = $this->createUrlGenerator($currentUrl, $addQueryString);

        $this->setExpectedException(RouteNotAllowedInContextException::class);
        $defectsJourneyUrlGenerator->toRemoveDefect($identifiedDefectId);
    }

    /**
     * @return array
     */
    public function testGoBackProvider()
    {
        return [
            [self::BROWSE_DEFECT_CATEGORY_URI, self::BROWSE_DEFECT_CATEGORY_URI],
            [self::BROWSE_DEFECT_CATEGORY_URI.'/111/edit', self::BROWSE_DEFECT_CATEGORY_URI],
            [self::BROWSE_DEFECT_CATEGORY_URI.'/111/remove', self::BROWSE_DEFECT_CATEGORY_URI],
            [self::BROWSE_DEFECT_CATEGORY_URI.'/add/111/advisory', self::BROWSE_DEFECT_CATEGORY_URI],
            [self::BROWSE_DEFECT_CATEGORY_URI.'/add/111/prs', self::BROWSE_DEFECT_CATEGORY_URI],
            [self::BROWSE_DEFECT_CATEGORY_URI.'/add/111/failure', self::BROWSE_DEFECT_CATEGORY_URI],
            [self::BROWSE_DEFECT_CATEGORY_URI.'/add/0/advisory', self::BROWSE_DEFECT_CATEGORY_URI],

            [self::BROWSE_DEFECT_CATEGORIES_URI, self::BROWSE_DEFECT_CATEGORIES_URI],
            [self::BROWSE_DEFECT_CATEGORIES_URI.'/111/edit', self::BROWSE_DEFECT_CATEGORIES_URI],
            [self::BROWSE_DEFECT_CATEGORIES_URI.'/111/remove', self::BROWSE_DEFECT_CATEGORIES_URI],
            [self::BROWSE_DEFECT_CATEGORIES_URI.'/111/edit', self::BROWSE_DEFECT_CATEGORIES_URI],
            [self::BROWSE_DEFECT_CATEGORIES_URI.'/add/0/advisory', self::BROWSE_DEFECT_CATEGORIES_URI],

            [self::SEARCH_DEFECT_URI, self::SEARCH_DEFECT_URI],
            [self::SEARCH_DEFECT_URI.'/111/edit', self::SEARCH_DEFECT_URI],
            [self::SEARCH_DEFECT_URI.'/111/remove', self::SEARCH_DEFECT_URI],
            [self::SEARCH_DEFECT_URI.'/add/111/advisory', self::SEARCH_DEFECT_URI],
            [self::SEARCH_DEFECT_URI.'/add/111/prs', self::SEARCH_DEFECT_URI],
            [self::SEARCH_DEFECT_URI.'/add/111/failure', self::SEARCH_DEFECT_URI],
            [self::SEARCH_DEFECT_URI.'/add/0/advisory', self::SEARCH_DEFECT_URI],

            [self::SEARCH_DEFECT_URI.'/111/edit'.self::SEARCH_QUERY, self::SEARCH_DEFECT_URI.self::SEARCH_QUERY, true],
            [self::SEARCH_DEFECT_URI.'/111/remove'.self::SEARCH_QUERY, self::SEARCH_DEFECT_URI.self::SEARCH_QUERY, true],
            [self::SEARCH_DEFECT_URI.'/add/111/prs'.self::SEARCH_QUERY, self::SEARCH_DEFECT_URI.self::SEARCH_QUERY, true],
            [self::SEARCH_DEFECT_URI.'/add/111/failure'.self::SEARCH_QUERY, self::SEARCH_DEFECT_URI.self::SEARCH_QUERY, true],
            [self::SEARCH_DEFECT_URI.'/add/111/advisory'.self::SEARCH_QUERY, self::SEARCH_DEFECT_URI.self::SEARCH_QUERY, true],
            [self::SEARCH_DEFECT_URI.'/add/0/advisory'.self::SEARCH_QUERY, self::SEARCH_DEFECT_URI.self::SEARCH_QUERY, true],

            [self::BASE_URI.'/111/remove', self::MOT_TEST_RESULT_URI],
            [self::BASE_URI.'/111/edit', self::MOT_TEST_RESULT_URI],
        ];
    }

    /**
     * @param $currentUrl
     * @param $expectedUrl
     * @param bool $addQueryString
     *
     * @throws RouteNotAllowedInContextException
     * @dataProvider testGoBackProvider
     */
    public function testGoBack($currentUrl, $expectedUrl, $addQueryString = false)
    {
        $defectsJourneyUrlGenerator = $this->createUrlGenerator($currentUrl, $addQueryString);

        $this->assertEquals($expectedUrl, $defectsJourneyUrlGenerator->goBack());
    }

    /**
     * @return array
     */
    public function testGoBackWithNoContextProvider()
    {
        return [
            [self::DEFECT_ID, ''],
            [self::DEFECT_ID, 'unknownURI'],
            [self::DEFECT_ID, 'unknownURI/other'],
        ];
    }

    /**
     * @param $currentUrl
     *
     * @throws RouteNotAllowedInContextException
     *
     * @dataProvider testGoBackWithNoContextProvider
     */
    public function testGoBackWithNoContext($currentUrl)
    {
        $defectsJourneyUrlGenerator = $this->createUrlGenerator($currentUrl);

        $this->setExpectedException(RouteNotAllowedInContextException::class);
        $defectsJourneyUrlGenerator->goBack();
    }

    /**
     * @param $currentUrl
     * @param bool $setUpQueryParams
     *
     * @return DefectsJourneyUrlGenerator
     */
    private function createUrlGenerator($currentUrl, $setUpQueryParams = false)
    {
        $this->setUpRequest($currentUrl, $setUpQueryParams);
        $contextProvider = $this->createContextProvider();

        return new DefectsJourneyUrlGenerator(
            $this->router,
            $this->request,
            $contextProvider
        );
    }
    /**
     * @return DefectsJourneyContextProvider
     */
    private function createContextProvider()
    {
        return new DefectsJourneyContextProvider(
            $this->router,
            $this->request
        );
    }

    /**
     * @param $currentUrl
     * @param bool $setUpQueryParam
     */
    private function setUpRequest($currentUrl, $setUpQueryParam = false)
    {
        $this->request->setMethod(Request::METHOD_GET);
        $this->request->setUri($currentUrl);
        if ($setUpQueryParam) {
            $params = new Parameters([
                'q' => 'testSearch',
                'p' => 10,
            ]);
            $this->request->setQuery($params);
        }
    }
}
