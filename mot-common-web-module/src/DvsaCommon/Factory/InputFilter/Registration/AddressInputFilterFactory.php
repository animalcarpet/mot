<?php
/**
 * This file is part of the DVSA MOT Common Web project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace DvsaCommon\Factory\InputFilter\Registration;

use DvsaCommon\InputFilter\Registration\AddressInputFilter;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * To initiate an instance of DetailsInputFilter
 * Class AddressInputFilterFactory
 */
class AddressInputFilterFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return AddressInputFilter
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $inputFilter = new AddressInputFilter();
        $inputFilter->init();

        return $inputFilter;
    }
}
