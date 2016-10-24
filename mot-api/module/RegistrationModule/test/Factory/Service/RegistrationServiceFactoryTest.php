<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Api\RegistrationModuleTest\Factory\Service;

use Doctrine\ORM\EntityManager;
use Dvsa\Mot\Api\RegistrationModule\Factory\Service\RegistrationServiceFactory;
use Dvsa\Mot\Api\RegistrationModule\Service\BusinessRoleAssigner;
use Dvsa\Mot\Api\RegistrationModule\Service\ContactDetailsCreator;
use Dvsa\Mot\Api\RegistrationModule\Service\OpenAMIdentityCreator;
use Dvsa\Mot\Api\RegistrationModule\Service\PersonCreator;
use Dvsa\Mot\Api\RegistrationModule\Service\RegistrationService;
use Dvsa\Mot\Api\RegistrationModule\Validator\RegistrationValidator;
use DvsaApplicationLogger\Log\Logger;
use DvsaCommonTest\TestUtils\XMock;
use MailerApi\Logic\UsernameCreator;
use PersonApi\Service\DuplicateEmailCheckerService;
use Zend\ServiceManager\ServiceManager;

/**
 * Class RegistrationServiceFactoryTest.
 */
class RegistrationServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateService()
    {
        $factory = new RegistrationServiceFactory();

        $serviceManager = new ServiceManager();

        $mockEntityManager = XMock::of(EntityManager::class);

        $serviceManager->setService(
            RegistrationValidator::class,
            XMock::of(RegistrationValidator::class)
        )->setService(
            EntityManager::class,
            $mockEntityManager
        )->setService(
            OpenAMIdentityCreator::class,
            XMock::of(OpenAMIdentityCreator::class)
        )->setService(
            PersonCreator::class,
            XMock::of(PersonCreator::class)
        )->setService(
            BusinessRoleAssigner::class,
            XMock::of(BusinessRoleAssigner::class)
        )->setService(
            ContactDetailsCreator::class,
            XMock::of(ContactDetailsCreator::class)
        )->setService(
            UsernameCreator::class,
            XMock::of(UsernameCreator::class)
        )->setService(
            'Application/Logger',
            XMock::of(Logger::class)
        )->setService(
            DuplicateEmailCheckerService::class,
            XMock::of(DuplicateEmailCheckerService::class)
        );

        $this->assertInstanceOf(
            RegistrationService::class,
            $factory->createService($serviceManager)
        );
    }
}
