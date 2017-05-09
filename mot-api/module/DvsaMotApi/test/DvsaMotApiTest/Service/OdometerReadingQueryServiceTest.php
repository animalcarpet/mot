<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotApiTest\Service;

use DvsaAuthentication\Identity;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use Api\Check\CheckMessage;
use Api\Check\CheckResult;
use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\Constants\OdometerUnit;
use DvsaCommon\Dto\Common\OdometerReadingDto;
use DvsaCommonApi\Authorisation\Assertion\ReadMotTestAssertion;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestType;
use DvsaMotApi\Service\OdometerReadingQueryService;
use DvsaMotApi\Service\Validator\Odometer\OdometerReadingDeltaAnomalyChecker;

/**
 * Class OdometerReadingQueryServiceTest.
 */
class OdometerReadingQueryServiceTest extends AbstractServiceTestCase
{
    /** @var OdometerReadingQueryService $queryService */
    private $queryService;

    /** @var OdometerReadingDeltaAnomalyChecker $anomalyChecker */
    private $anomalyChecker;

    /** @var AuthorisationServiceInterface $authService */
    private $authorizationService;

    /** @var AuthenticationServiceInterface */
    private $identityProvider;

    /** @var MotTestRepository */
    private $motTestRepository;

    public function setUp()
    {
        $this->authorizationService = XMock::of(\DvsaAuthorisation\Service\AuthorisationServiceInterface::class);
        $this->identityProvider = XMock::of(\Zend\Authentication\AuthenticationService::class);
        $this->identityProvider->expects($this->any())
            ->method('getIdentity')
            ->will($this->returnValue($this->getTestIdentity()));
        $this->anomalyChecker = XMock::of(OdometerReadingDeltaAnomalyChecker::class);

        $this->motTestRepository = XMock::of(\DvsaEntities\Repository\MotTestRepository::class);
        $this->motTestRepository->expects($this->any())
            ->method('isTesterForMot')
            ->will($this->returnValue(true));

        $this->queryService = new OdometerReadingQueryService(
            $this->anomalyChecker,
            $this->authorizationService,
            new ReadMotTestAssertion(
                $this->authorizationService,
                $this->identityProvider
            ),
            $this->motTestRepository,
            $this->identityProvider
        );
    }

    public function testGetNoticesGivenNoReadingFoundShouldReturnEmptyArray()
    {
        // given
        $motTestNumber = 4;
        $this->currentReadingOf(null);
        $this->previousReadingOf(null);

        $this->setMockMotTestNumber($motTestNumber);

        // when
        $result = $this->queryService->getNotices($motTestNumber);

        // then
        $this->assertEquals([], $result, 'An empty array should be returned');
    }

    public function testGetNoticesGivenCurrentAndPreviousReadingsFoundShouldReturnArrayOfStringsOffAnomalyCheckResult()
    {
        //given
        $motTestNumber = 33;
        $anyOdometerReading = (new OdometerReadingDto())
            ->setValue(123456)
            ->setUnit(OdometerUnit::MILES)
            ->setResultType(OdometerReadingResultType::OK)
            ->setIssuedDate(new \DateTime());

        $anomalyText = OdometerReadingDeltaAnomalyChecker::CURRENT_EQ_PREVIOUS;
        $this->currentReadingOf($anyOdometerReading);
        $this->previousReadingOf($anyOdometerReading);
        $this->anomalyCheckerResult(CheckResult::with(CheckMessage::withText($anomalyText)));

        $this->setMockMotTestNumber($motTestNumber);

        // when
        $result = $this->queryService->getNotices($motTestNumber);

        // then
        $this->assertEquals([$anomalyText], $result, 'Returned array does not match check result message');
    }

    private function currentReadingOf($result)
    {
        $this->motTestRepository->expects($this->any())
            ->method('findReadingForTest')
            ->will($this->returnValue($result));
    }

    private function previousReadingOf($result)
    {
        $this->motTestRepository->expects($this->any())
            ->method('findPreviousReading')
            ->will($this->returnValue($result));
    }

    private function anomalyCheckerResult($result)
    {
        $this->anomalyChecker->expects($this->any())
            ->method('check')
            ->will($this->returnValue($result));
    }

    private function getTestIdentity()
    {
        $person = $this->getMockPerson();
        $person->setId(3);
        $person->setUsername('user');
        $identity = new Identity($person);

        return $identity;
    }

    /**
     * @param $testNumber
     *
     * @return MotTest
     */
    private function getMotTest($testNumber)
    {
        $motTest = new MotTest();
        $motTest->setNumber($testNumber)
            ->setMotTestType(new MotTestType())
            ->setTester($this->getTestIdentity()->getPerson());

        return $motTest;
    }

    private function setMockMotTestNumber($motTestNumber)
    {
        $this->motTestRepository->expects($this->any())
            ->method('getMotTestByNumber')
            ->will($this->returnValue($this->getMotTest($motTestNumber)));
    }
}
