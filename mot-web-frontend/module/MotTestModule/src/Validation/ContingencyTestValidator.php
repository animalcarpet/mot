<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\Validation;

use DvsaCommon\Validation\CommonContingencyTestValidator;

/**
 * ContingencyTest Validator.
 */
class ContingencyTestValidator extends CommonContingencyTestValidator
{
    public function __construct($infinityContingencyFlag)
    {
        parent::__construct($infinityContingencyFlag);
    }
}
