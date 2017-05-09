<?php

namespace DvsaMotTest\Model;

use Zend\Form\Annotation;

/**
 * @Annotation\Hydrator("Zend\Stdlib\Hydrator\ObjectProperty")
 * @Annotation\Name("OdometerUpdate")
 */
class OdometerUpdate
{
    /**
     * @Annotation\Type("Zend\Form\Element\Number")
     * @Annotation\AllowEmpty({"true"})
     * @Annotation\Filter({"name":"StripTags"})
     * @Annotation\Validator({"name":"Digits"})
     * @Annotation\Validator({"name":"Between","options":{"min": 0,"max": 999999}})
     */
    public $odometer;

    /**
     * @Annotation\Type("Zend\Form\Element\Select")
     * @Annotation\Options({"label":"Genre", "value_options":{
     *   "mi":"Miles",
     *   "km"   :"km",
     * }
     * })
     */
    public $unit;

    /**
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true" })
     */
    public $resultType;

    /**
     * @Annotation\Type("Zend\Form\Element\Button")
     */
    public $submit;

    public function exchangeArray($data)
    {
        $this->odometer = (!empty($data['odometer'])) ? $data['odometer'] : null;
    }
}
