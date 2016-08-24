<?php
/**
 * This file is part of the DVSA MOT Common Web project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace DvsaCommon\Factory\InputFilter\Registration;

use DvsaCommon\InputFilter\Registration\DetailsInputFilter;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * To initiate an instance of DetailsInputFilter
 * Class DetailsInputFilterFactory
 */
class DetailsInputFilterFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return DetailsInputFilter
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $inputFilter = new DetailsInputFilter();
        $inputFilter->init();

        return $inputFilter;
    }
}
