<?php
/**
 * This file is part of the DVSA MOT Common Web project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace DvsaCommon\Factory\InputFilter\Registration;

use DvsaCommon\InputFilter\Registration\SecurityQuestionFirstInputFilter;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * To initiate an instance of DetailsInputFilter
 * Class SecurityQuestionFirstInputFilterFactory
 */
class SecurityQuestionFirstInputFilterFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return SecurityQuestionFirstInputFilter
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $inputFilter = new SecurityQuestionFirstInputFilter();
        $inputFilter->init();

        return $inputFilter;
    }
}
