<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\PersonModuleTest\View;

use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\PersonModule\View\PersonProfileUrlGenerator;
use DvsaCommon\Auth\MotIdentityInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use PHPUnit_Framework_TestCase;
use Zend\Http\PhpEnvironment\Request;
use Zend\Mvc\Router\Http\TreeRouteStack;

class PersonProfileUrlGeneratorTest extends PHPUnit_Framework_TestCase
{
    const LOGGED_IN_PERSON_ID = 105;
    const DEFAULT_URL = '/your-profile';

    /**
     * @var MotIdentityProviderInterface
     */
    private $identityProvider;

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
        $this->router->setRoutes(require __DIR__.'/Fixtures/routes.php');
        $this->request = new Request();

        $this->identityProvider = $this
            ->getMockBuilder(MotIdentityProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MotIdentityInterface $motIdentity */
        $motIdentity = $this->getMockBuilder(MotIdentityInterface::class)->disableOriginalConstructor()->getMock();
        $motIdentity
            ->method('getUserId')
            ->willReturn(self::LOGGED_IN_PERSON_ID);
        $this
            ->identityProvider
            ->method('getIdentity')
            ->willReturn($motIdentity);
    }

    /**
     * @return array
     */
    public function toPersonProfileProvider()
    {
        return [
            ['/your-profile/trade-roles', '/your-profile'],
            ['/authorised-examiner/1/user/105/trade-roles', '/authorised-examiner/1/user/105'],
            ['/vehicle-testing-station/2/user/105/trade-roles', '/vehicle-testing-station/2/user/105'],
            ['/user-admin/user/105/trade-roles', '/user-admin/user/105'],
            ['/unknown/url', self::DEFAULT_URL],
        ];
    }

    /**
     * @param string $currentUrl
     * @param string $generatedUrl
     *
     * @dataProvider toPersonProfileProvider
     */
    public function testToPersonProfile($currentUrl, $generatedUrl)
    {
        $this->setBaseUrl($currentUrl);
        $contextProvider = new ContextProvider($this->router, $this->request);
        $personProfileUrlGenerator = new PersonProfileUrlGenerator($this->router, $this->request, $contextProvider,
            $this->identityProvider);

        $this->assertEquals($generatedUrl, $personProfileUrlGenerator->toPersonProfile());
    }

    /**
     * @return array
     */
    public function toPersonProfileWithUnknownContextProvider()
    {
        return [
            ['/your-profile/trade-roles', self::DEFAULT_URL],
            ['/authorised-examiner/1/user/105/trade-roles', self::DEFAULT_URL],
            ['/vehicle-testing-station/2/user/105/trade-roles', self::DEFAULT_URL],
            ['/user-admin/user/105/trade-roles', self::DEFAULT_URL],
            ['/unknown/url', self::DEFAULT_URL],
        ];
    }

    /**
     * @param string $currentUrl
     * @param string $generatedUrl
     *
     * @dataProvider toPersonProfileWithUnknownContextProvider
     */
    public function testToPersonProfileWithUnknownContext($currentUrl, $generatedUrl)
    {
        $this->setBaseUrl($currentUrl);

        /** @var ContextProvider $contextProvider */
        $contextProvider = $this->getMockBuilder(ContextProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextProvider
            ->method('getContext')
            ->willReturn(ContextProvider::NO_CONTEXT);

        $personProfileUrlGenerator = new PersonProfileUrlGenerator($this->router, $this->request, $contextProvider,
            $this->identityProvider);

        $this->assertEquals($generatedUrl, $personProfileUrlGenerator->toPersonProfile());
    }

    /**
     * @return array
     */
    public function fromPersonProfileProvider()
    {
        return [
            ['/your-profile', ['trade-roles'], '/your-profile/trade-roles'],
            ['/authorised-examiner/1/user/105', ['trade-roles'], '/authorised-examiner/1/user/105/trade-roles'],
            ['/vehicle-testing-station/2/user/105', ['trade-roles'], '/vehicle-testing-station/2/user/105/trade-roles'],
            ['/user-admin/user/105', ['trade-roles'], '/user-admin/user/105/trade-roles'],
            ['/unknown/url', [''], self::DEFAULT_URL],
            ['/unknown/url', ['trade-roles'], self::DEFAULT_URL],
        ];
    }

    /**
     * @param string $currentUrl
     * @param array  $args         Argument passed to `fromPersonProfile()`
     * @param string $generatedUrl
     *
     * @dataProvider fromPersonProfileProvider
     */
    public function testFromPersonProfile($currentUrl, array $args, $generatedUrl)
    {
        if (!isset($args[0])) {
            $this->fail(sprintf('%s requires $args[0] ($subRouteName) to bet set.', __METHOD__));
        }
        $subRouteName = $args[0];
        $params = isset($args[1]) ? $args[1] : [];
        $options = isset($args[1]) ? $args[2] : [];

        $this->setBaseUrl($currentUrl);
        $contextProvider = new ContextProvider($this->router, $this->request);
        $personProfileUrlGenerator = new PersonProfileUrlGenerator($this->router, $this->request, $contextProvider,
            $this->identityProvider);
        $this->assertEquals($generatedUrl, $personProfileUrlGenerator->fromPersonProfile($subRouteName, $params,
            $options));
    }

    /**
     * @return array
     */
    public function fromPersonProfileWithUnknownContextProvider()
    {
        return [
            ['/your-profile', ['trade-roles'], self::DEFAULT_URL],
            ['/authorised-examiner/1/user/105', ['trade-roles'], self::DEFAULT_URL],
            ['/vehicle-testing-station/2/user/105', ['trade-roles'], self::DEFAULT_URL],
            ['/user-admin/user/105', ['trade-roles'], self::DEFAULT_URL],
            ['/unknown/url', [''], self::DEFAULT_URL],
            ['/unknown/url', ['trade-roles'], self::DEFAULT_URL],
        ];
    }

    /**
     * @param string $currentUrl
     * @param array  $args         Argument passed to `fromPersonProfile()`
     * @param string $generatedUrl
     *
     * @dataProvider fromPersonProfileWithUnknownContextProvider
     */
    public function testFromPersonProfileWithUnknownContext($currentUrl, array $args, $generatedUrl)
    {
        if (!isset($args[0])) {
            $this->fail(sprintf('%s requires $args[0] ($subRouteName) to bet set.', __METHOD__));
        }
        $subRouteName = $args[0];
        $params = isset($args[1]) ? $args[1] : [];
        $options = isset($args[1]) ? $args[2] : [];

        $this->setBaseUrl($currentUrl);

        /** @var ContextProvider $contextProvider */
        $contextProvider = $this->getMockBuilder(ContextProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextProvider
            ->method('getContext')
            ->willReturn(ContextProvider::NO_CONTEXT);

        $personProfileUrlGenerator = new PersonProfileUrlGenerator($this->router, $this->request, $contextProvider,
            $this->identityProvider);

        $this->assertEquals($generatedUrl, $personProfileUrlGenerator->fromPersonProfile($subRouteName, $params,
            $options));
    }

    /**
     * @param string $baseUrl
     */
    private function setBaseUrl($baseUrl)
    {
        $this->request->setMethod(Request::METHOD_GET);
        $this->request->setUri($baseUrl);
    }
}
