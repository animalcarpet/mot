<?php

namespace DvsaCommonApiTest\Service;

use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\MockHandler;
use DvsaCommonTest\TestUtils\TestCaseTrait;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Person;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

/**
 * Class AbstractServiceTestCase.
 */
abstract class AbstractServiceTestCase extends PHPUnit_Framework_TestCase
{
    const AT = 'at';
    const WITH = 'with';
    const WILL = 'will';
    const METHOD = 'method';

    use TestCaseTrait;

    /**
     * @param EntityManager|MockObj $mock
     * @param string                $method
     * @param                       $returnValue
     * @param null                  $with
     * @param bool                  $once
     *
     * @return mixed
     */
    protected function setupMockForCalls(
        $mock,
        $method,
        $returnValue,
        $with = null,
        $once = false
    ) {
        $times = $once ? $this->once() : $this->any();
        if (!$with) {
            $mock->expects($times)
                ->method($method)
                ->will($this->returnValue($returnValue));
        } else {
            $mock->expects($times)
                ->method($method)
                ->with($with)
                ->will($this->returnValue($returnValue));
        }

        return $mock;
    }

    protected function setupMockForSingleCall(
        $mock,
        $method,
        $returnValue,
        $with = null
    ) {
        return $this->setupMockForCalls($mock, $method, $returnValue, $with, true);
    }

    /**
     * @param EntityManager|MockObj $mock
     * @param array                 $methodsParams
     */
    protected function setupMockForSpecificCalls($mock, $methodsParams)
    {
        foreach ($methodsParams as $methodParams) {
            $mock->expects($this->at($methodParams[self::AT]))
                ->method($methodParams[self::METHOD])
                ->with($methodParams[self::WITH])
                ->will($methodParams[self::WILL]);
        }
    }

    protected function setupHydrator($mockHydrator, $returnValue, $with, $method = 'extract')
    {
        $this->setupMockForSingleCall($mockHydrator, $method, $returnValue, $with);
    }

    protected function setupHydratorExtract($mockHydrator, $returnValue, $with)
    {
        $this->setupHydrator($mockHydrator, $returnValue, $with);
    }

    /**
     * @param MockObj $mockHydrator
     * @param string  $method
     *
     * @return MockObj
     */
    protected function setupHydratorForAnyCalls($mockHydrator, $method = 'extract')
    {
        $mockHydrator->expects($this->any())->method($method);

        return $mockHydrator;
    }

    protected function setupHandlerForHydratorMultipleCalls(&$mockHydrator, $calls, $methodDefault = 'extract')
    {
        $mockHandler = new MockHandler($mockHydrator, $this);
        foreach ($calls as $call) {
            $mockHandler->next(isset($call[self::METHOD]) ? $call[self::METHOD] : $methodDefault)
                ->with($call[self::WITH])
                ->will($this->returnValue($call[self::WILL]));
        }

        return $mockHandler;
    }

    protected function getMockEntityManagerWithRepository($mockRepository, $repositoryClass)
    {
        $mockEntityManager = $this->getMockEntityManager();
        $this->setupMockForCalls($mockEntityManager, 'getRepository', $mockRepository, $repositoryClass);

        return $mockEntityManager;
    }

    protected function setupMockEntityManagerToGetRepository(
        &$mockEntityManager,
        $mockRepository,
        $repositoryClass = \Doctrine\ORM\EntityRepository::class
    ) {
        $this->setupMockForSingleCall($mockEntityManager, 'getRepository', $mockRepository, $repositoryClass);
    }

    /**
     * @return EntityManager|MockObj
     */
    protected function getMockEntityManager()
    {
        return $this->getMockWithDisabledConstructor(EntityManager::class);
    }

    protected function getMockRepository($repositoryClass = \Doctrine\ORM\EntityRepository::class)
    {
        return $this->getMockWithDisabledConstructor($repositoryClass);
    }

    protected function getMockHydrator()
    {
        return $this->getMockWithDisabledConstructor(\DoctrineModule\Stdlib\Hydrator\DoctrineObject::class);
    }

    /**
     * @return Person|MockObj
     */
    protected function getMockPerson()
    {
        return new Person();
    }

    /**
     * @return \Zend\ServiceManager\ServiceManager
     */
    protected function getServiceManager()
    {
        return Bootstrap::getServiceManager();
    }

    /**
     * @param bool $isAuthorized
     *
     * @return AuthorisationServiceInterface|MockObj
     *
     * @throws \Exception
     */
    protected function getMockAuthorizationService($isAuthorized = true)
    {
        $authorizationService = XMock::of(\DvsaAuthorisation\Service\AuthorisationServiceInterface::class);
        if ($isAuthorized) {
            $this->setupMockForCalls($authorizationService, 'isGranted', true);
        }

        return $authorizationService;
    }

    protected function getMockQuery()
    {
        return $this->getMockForAbstractClass(
            \Doctrine\ORM\AbstractQuery::class,
            [],
            '',
            false,
            true,
            true,
            ['setParameter', 'getResult', 'execute']
        );
    }

    /**
     * @param $mockClass
     *
     * @return MockObj
     */
    protected function getMockWithDisabledConstructor($mockClass)
    {
        return \DvsaCommonTest\TestUtils\XMock::of($mockClass);
    }

    protected function getRepositoryMock()
    {
        return $this->getMockWithDisabledConstructor(\Doctrine\ORM\EntityRepository::class);
    }

    /**
     * @param MockObj $repositoryMock
     * @param mixed   $with
     * @param null    $will
     *
     * @return mixed
     */
    protected function callFindBy($repositoryMock, $with, $will = null)
    {
        return $repositoryMock->expects($this->once())->method('findBy')->with($with)->will($this->returnValue($will));
    }

    public function dateAttributeEqualTo(
        $attributeName,
        $value,
        $delta = 0,
        $maxDepth = 10,
        $canonicalize = false,
        $ignoreCase = false
    ) {
        return self::attribute(
            self::dateEqualTo(
                $value,
                $delta,
                $maxDepth,
                $canonicalize,
                $ignoreCase
            ),
            $attributeName
        );
    }

    public static function dateEqualTo($value, $delta = 0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false)
    {
        return new DateIsEqual(
            $value, $delta, $maxDepth, $canonicalize, $ignoreCase
        );
    }

    /**
     * Mock class protected or private property.
     *
     * @param object $instance Instance of class
     * @param string $property Name of property
     * @param mixed  $value    Set value to property
     * @param string $class    Name of class, if not provided then take name of class from instance
     *
     * @deprecated use XMock::mockClassField()
     */
    protected function mockClassField($instance, $property, $value, $class = null)
    {
        $r = new ReflectionClass($class ? $class : get_class($instance));

        $prop = $r->getProperty($property);
        $prop->setAccessible(true);
        $prop->setValue($instance, $value);
    }

    protected function assertGrantedAtSite(MockObj $authService, $permissions, $siteId)
    {
        $authService->expects($this->any())
            ->method('assertGrantedAtSite')
            ->willReturnCallback(
                function ($chkPermission, $chkSiteId) use (&$permissions, $siteId) {
                    if ($chkSiteId == $siteId && !in_array($chkPermission, $permissions)) {
                        throw new UnauthorisedException('You not have permissions');
                    }

                    return true;
                }
            );
    }

    protected function assertGrantedAtOrganisation(MockObj $authService, $permissions, $orgId)
    {
        $authService->expects($this->any())
            ->method('assertGrantedAtOrganisation')
            ->willReturnCallback(
                function ($chkPermission, $chkOrgId) use (&$permissions, $orgId) {
                    if ($chkOrgId === $orgId && !in_array($chkPermission, $permissions)) {
                        throw new UnauthorisedException('You not have permissions');
                    }

                    return true;
                }
            );
    }
}
