<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Api\RegistrationModuleTest\Factory\Validator;

use Dvsa\Mot\Api\RegistrationModule\Factory\Validator\RegistrationValidatorFactory;
use Dvsa\Mot\Api\RegistrationModule\Validator\RegistrationValidator;
use DvsaCommon\InputFilter\Registration\ContactDetailsInputFilter;
use DvsaCommon\InputFilter\Registration\DetailsInputFilter;
use DvsaCommon\InputFilter\Registration\PasswordInputFilter;
use DvsaCommon\InputFilter\Registration\SecurityQuestionFirstInputFilter;
use DvsaCommon\InputFilter\Registration\SecurityQuestionSecondInputFilter;
use DvsaCommonTest\TestUtils\XMock;
use Zend\ServiceManager\ServiceManager;

/**
 * Class RegistrationValidatorFactoryTest.
 */
class RegistrationServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateService()
    {
        $factory = new RegistrationValidatorFactory();

        $serviceManager = new ServiceManager();

        $serviceManager->setService(
                DetailsInputFilter::class,
                XMock::of(DetailsInputFilter::class)
            )->setService(
                ContactDetailsInputFilter::class,
                XMock::of(ContactDetailsInputFilter::class)
            )->setService(
                PasswordInputFilter::class,
                XMock::of(PasswordInputFilter::class)
            )->setService(
                SecurityQuestionFirstInputFilter::class,
                XMock::of(SecurityQuestionFirstInputFilter::class)
            )->setService(
                SecurityQuestionSecondInputFilter::class,
                XMock::of(SecurityQuestionSecondInputFilter::class)
            );

        $this->assertInstanceOf(
            RegistrationValidator::class,
            $factory->createService($serviceManager)
        );
    }
}
