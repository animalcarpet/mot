<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Api\RegistrationModule\Factory\Validator;

use Dvsa\Mot\Api\RegistrationModule\Validator\RegistrationValidator;
use DvsaCommon\InputFilter\Registration\AddressInputFilter;
use DvsaCommon\InputFilter\Registration\DetailsInputFilter;
use DvsaCommon\InputFilter\Registration\PasswordInputFilter;
use DvsaCommon\InputFilter\Registration\SecurityQuestionFirstInputFilter;
use DvsaCommon\InputFilter\Registration\SecurityQuestionSecondInputFilter;
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
        $detailsInputFilter = new DetailsInputFilter();
        $addressInputFilter = new AddressInputFilter();
        $passwordInputFilter = new PasswordInputFilter();
        $securityQuestionFirstInputFilter = new SecurityQuestionFirstInputFilter();
        $securityQuestionSecondInputFilter = new SecurityQuestionSecondInputFilter();

        $detailsInputFilter->init();
        $addressInputFilter->init();
        $passwordInputFilter->init();
        $securityQuestionFirstInputFilter->init();
        $securityQuestionSecondInputFilter->init();

        $service = new RegistrationValidator(
            $detailsInputFilter,
            $addressInputFilter,
            $passwordInputFilter,
            $securityQuestionFirstInputFilter,
            $securityQuestionSecondInputFilter
        );

        return $service;
    }
}
