<?php

namespace AccountTest\Logic;

use DvsaClient\ViewModel\AbstractFormModel;

class PasswordPolicyTestFormModel extends AbstractFormModel
{
    public function isValid()
    {
        return false;
    }
}
