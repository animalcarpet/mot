<?php

namespace DvsaMotTest\Form;

use Zend\Form\Annotation;

/**
 * @Annotation\Hydrator("Zend\Stdlib\Hydrator\ObjectProperty")
 * @Annotation\Name("VehicleSearch")
 */
class VehicleSearch
{
    /**
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\AllowEmpty({"true"})
     * @Annotation\Filter({"name":"StripTags"})
     * @Annotation\Options({"label":"Registration number"})
     */
    public $registration;

    /**
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Options({"label":"VIN/Chassis Number"})
     * @Annotation\AllowEmpty({"true"})
     */
    public $vin;

    /**
     * @Annotation\Type("Zend\Form\Element\Submit")
     * @Annotation\Attributes({"value":"Search"})
     */
    public $submit;

    public function exchangeArray($data)
    {
        $this->registration = (!empty($data['registration'])) ? $data['registration'] : null;
        $this->vin = (!empty($data['vin'])) ? $data['vin'] : null;
    }
}
