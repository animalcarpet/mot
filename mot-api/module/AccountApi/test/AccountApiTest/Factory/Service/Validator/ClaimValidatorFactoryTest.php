<?php

namespace AccountApiTest\Factory\Service\Validator;

use AccountApi\Factory\Service\Validator\ClaimValidatorFactory;
use AccountApi\Service\SecurityQuestionService;
use AccountApi\Service\Validator\ClaimValidator;
use Doctrine\ORM\EntityManager;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Repository\SecurityQuestionRepository;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ClaimValidatorFactoryTest.
 */
class ClaimValidatorFactoryTest extends \PHPUnit_Framework_TestCase
{
    /* @var ClaimValidatorFactory $serviceFactory */
    protected $serviceFactory;

    protected $serviceLocatorMock;
    protected $entityManager;
    protected $securityQuestion;
    protected $securityQuestionRepo;

    public function setUp()
    {
        $this->serviceFactory = new ClaimValidatorFactory();
        $this->serviceLocatorMock = XMock::of(ServiceLocatorInterface::class, ['get']);

        $this->entityManager = XMock::of(EntityManager::class);
        $this->securityQuestion = XMock::of(SecurityQuestionService::class);

        $this->securityQuestionRepo = XMock::of(SecurityQuestionRepository::class);
    }

    public function testCreateService()
    {
        $this->serviceLocatorMock->expects($this->at(0))
            ->method('get')
            ->willReturn($this->securityQuestion);
        $this->serviceLocatorMock->expects($this->at(1))
            ->method('get')
            ->willReturn($this->entityManager);

        $this->entityManager->expects($this->at(0))
            ->method('getRepository')
            ->willReturn($this->securityQuestionRepo);

        $this->assertInstanceOf(
            ClaimValidator::class,
            $this->serviceFactory->createService($this->serviceLocatorMock)
        );
    }
}
