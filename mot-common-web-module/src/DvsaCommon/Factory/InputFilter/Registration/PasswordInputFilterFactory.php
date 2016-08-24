<?php
/**
 * This file is part of the DVSA MOT Common Web project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace DvsaCommon\Factory\InputFilter\Registration;

use DvsaCommon\InputFilter\Registration\AddressInputFilter;
use DvsaCommon\InputFilter\Registration\PasswordInputFilter;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * To initiate an instance of DetailsInputFilter
 * Class PasswordInputFilterFactory
 */
class PasswordInputFilterFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return PasswordInputFilter
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $inputFilter = new PasswordInputFilter();
        $inputFilter->init();

        return $inputFilter;
    }
}
