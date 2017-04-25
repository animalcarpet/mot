<?php
/**
 * This file is part of the DVSA MOT Common Web project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace DvsaCommon\Factory\InputFilter\Registration;

use DvsaCommon\InputFilter\Registration\SecurityQuestionsInputFilter;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * To initiate an instance of SecurityQuestionsInputFilter.
 *
 * Class SecurityQuestionsInputFilterFactory
 */
class SecurityQuestionsInputFilterFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return SecurityQuestionsInputFilter
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $inputFilter = new SecurityQuestionsInputFilter();
        $inputFilter->init();

        return $inputFilter;
    }
}
