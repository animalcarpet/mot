<?php

namespace Core\TwoStepForm;

interface ProcessBuilderInterface
{
    /**
     * @param $propertyName
     *
     * @return SingleStepProcessInterface
     */
    public function get($propertyName);
}
