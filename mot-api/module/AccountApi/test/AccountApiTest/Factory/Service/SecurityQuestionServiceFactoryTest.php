<?php
namespace AccountApiTest\Service\Factory;

use AccountApi\Service\Validator\PersonSecurityAnswerValidator;
use Doctrine\ORM\EntityManager;
use Dvsa\Mot\Api\RegistrationModule\Service\PersonSecurityAnswerRecorder;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\TestCaseTrait;
use DvsaCommonTest\TestUtils\XMock;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use AccountApi\Factory\Service\SecurityQuestionServiceFactory;
use AccountApi\Service\SecurityQuestionService;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\PersonSecurityAnswer;
use DvsaEntities\Entity\SecurityQuestion;
use DvsaEntities\Repository\PersonRepository;
use DvsaEntities\Repository\PersonSecurityAnswerRepository;
use DvsaEntities\Repository\SecurityQuestionRepository;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Class SecurityQuestionServiceFactoryTest
 *
 * @package AccountApiTest\Service\Factory
 */
class SecurityQuestionServiceFactoryTest extends AbstractServiceTestCase
{
    use TestCaseTrait;

    public function testEventServiceGetList()
    {
        $serviceManager = new ServiceManager();

        $mockEntityManager = XMock::of(EntityManager::class);
        $serviceManager->setService(EntityManager::class, $mockEntityManager);

        $repoMap = [
            SecurityQuestion::class => XMock::of(SecurityQuestionRepository::class),
            Person::class => XMock::of(PersonRepository::class),
            PersonSecurityAnswer::class => XMock::of(PersonSecurityAnswerRepository::class)
        ];
        $this->mockMethod($mockEntityManager, 'getRepository', $this->any(), function() use ($repoMap) {
            $className = func_get_args()[0];
            return isset($repoMap[$className]) ? $repoMap[$className] : null;
        });

        $mockPersonSecurityAnswerRecorder = XMock::of(PersonSecurityAnswerRecorder::class);
        $serviceManager->setService(PersonSecurityAnswerRecorder::class, $mockPersonSecurityAnswerRecorder);

        $mockPersonSecurityAnswerValidator = XMock::of(PersonSecurityAnswerValidator::class);
        $serviceManager->setService(PersonSecurityAnswerValidator::class, $mockPersonSecurityAnswerValidator);

        $mockParamObfuscator = XMock::of(ParamObfuscator::class);
        $serviceManager->setService(ParamObfuscator::class, $mockParamObfuscator);

        $factory = new SecurityQuestionServiceFactory();

        $this->assertInstanceOf(
            SecurityQuestionService::class,
            $factory->createService($serviceManager)
        );
    }
}
