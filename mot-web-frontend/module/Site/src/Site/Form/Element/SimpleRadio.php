<?php

namespace Site\Form\Element;

use Zend\Form\Element\Checkbox;

class SimpleRadio extends Checkbox
{
    /** @var bool */
    protected $checked = false;

    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    public function setChecked($checked)
    {
        $this->checked = $checked;
    }

    public function isChecked()
    {
        return $this->checked;
    }
}