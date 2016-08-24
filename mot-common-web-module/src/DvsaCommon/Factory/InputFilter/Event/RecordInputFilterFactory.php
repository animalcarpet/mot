<?php
/**
 * Created by PhpStorm.
 * User: chrislo
 * Date: 10/09/15
 * Time: 11:39
 */

namespace DvsaCommon\Factory\InputFilter\Event;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;
use DvsaCommon\InputFilter\Event\RecordInputFilter;

class RecordInputFilterFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return OutcomeInputFilter
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $inputFilter = new RecordInputFilter();
        $inputFilter->init();

        return $inputFilter;
    }
}
