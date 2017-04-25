<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Api\RegistrationModule\Factory\Validator;

use Dvsa\Mot\Api\RegistrationModule\Validator\RegistrationValidator;
use DvsaCommon\InputFilter\Registration\ContactDetailsInputFilter;
use DvsaCommon\InputFilter\Registration\DetailsInputFilter;
use DvsaCommon\InputFilter\Registration\EmailInputFilter;
use DvsaCommon\InputFilter\Registration\PasswordInputFilter;
use DvsaCommon\InputFilter\Registration\SecurityQuestionsInputFilter;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class RegistrationValidatorFactory.
 */
class RegistrationValidatorFactory implements FactoryInterface
{
    /**
     * Create service.
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $emailInputFilter = new EmailInputFilter();
        $detailsInputFilter = new DetailsInputFilter();
        $contactDetailsInputFilter = new ContactDetailsInputFilter();
        $passwordInputFilter = new PasswordInputFilter();
        $securityQuestionsInputFilter = new SecurityQuestionsInputFilter();

        $emailInputFilter->init();
        $detailsInputFilter->init();
        $contactDetailsInputFilter->init();
        $passwordInputFilter->init();
        $securityQuestionsInputFilter->init();

        $service = new RegistrationValidator(
            $emailInputFilter,
            $detailsInputFilter,
            $contactDetailsInputFilter,
            $passwordInputFilter,
            $securityQuestionsInputFilter
        );

        return $service;
    }
}
