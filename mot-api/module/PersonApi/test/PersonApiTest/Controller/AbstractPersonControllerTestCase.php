<?php

namespace PersonApiTest\Controller;

use DvsaCommonApiTest\Controller\ApiControllerUnitTestInterface;
use DvsaCommonApiTest\Controller\ApiControllerUnitTestTrait;
use OrganisationApi\Service\AuthorisedExaminerService;
use PersonApi\Dto\MotTestingAuthorisationCollector;
use PersonApi\Service\PersonalAuthorisationForMotTestingService;
use PersonApi\Service\PersonalDetailsService;
use PersonApi\Service\PersonService;

/**
 * Base for common application controller test in PersonApi namespace.
 */
abstract class AbstractPersonControllerTestCase extends \PHPUnit_Framework_TestCase implements
    ApiControllerUnitTestInterface
{
    use ApiControllerUnitTestTrait;

    public function mockServices()
    {
        $this->createMock(AuthorisedExaminerService::class);
        $service = $this->createMock(PersonalAuthorisationForMotTestingService::class);
        $this->createMock(PersonalDetailsService::class);
        $this->createMock(PersonService::class);

        $service->expects($this->any())
            ->method('updatePersonalTestingAuthorisationGroup')
            ->will($this->returnValue(new MotTestingAuthorisationCollector([])));
        $service->expects($this->any())
            ->method('getPersonalTestingAuthorisation')
            ->will($this->returnValue(new MotTestingAuthorisationCollector([])));
    }
}
