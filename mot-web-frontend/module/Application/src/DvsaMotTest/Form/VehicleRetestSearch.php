<?php

namespace DvsaMotTest\Form;

use Zend\Form\Annotation;

/**
 * @Annotation\Hydrator("Zend\Stdlib\Hydrator\ObjectProperty")
 * @Annotation\Name("VehicleSearch")
 */
class VehicleRetestSearch
{
    /**
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\AllowEmpty({"true"})
     * @Annotation\Filter({"name":"StripTags"})
     * @Annotation\Options({"label":"Registration number"})
     */
    public $registration;

    /**
     * @Annotation\Type("Zend\Form\Element\Select")
     * @Annotation\AllowEmpty({"true"})
     * @Annotation\Options({"label":"Vin type"})
     * @Annotation\Attributes({"options":{"partialVin":"Partial VIN","fullVin":"Full VIN","noVin":"No VIN"}})
     */
    public $vinType;

    /**
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Options({"label":"VIN/Chassis Number"})
     * @Annotation\AllowEmpty({"true"})
     */
    public $vin;

    /**
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\AllowEmpty({"true"})
     * @Annotation\Options({"label":"Enter previous test number"})
     */
    public $testNumber;

    /**
     * @Annotation\Type("Zend\Form\Element\Submit")
     * @Annotation\Attributes({"value":"Search"})
     */
    public $submit;

    /**
     * @Annotation\Type("Zend\Form\Element\Hidden")
     */
    public $previousSearchRegistration;

    /**
     * @Annotation\Type("Zend\Form\Element\Hidden")
     */
    public $previousSearchVin;

    public function exchangeArray($data)
    {
        $this->registration = (!empty($data['registration'])) ? $data['registration'] : null;
        $this->vinType = (!empty($data['vinType'])) ? $data['vinType'] : null;
        $this->vin = (!empty($data['vin'])) ? $data['vin'] : null;
        $this->testNumber = (!empty($data['testNumber'])) ? $data['testNumber'] : null;
    }
}
