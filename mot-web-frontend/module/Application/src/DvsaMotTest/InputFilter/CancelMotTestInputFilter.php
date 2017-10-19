<?php

namespace DvsaMotTest\InputFilter;

use DvsaCommon\Enum\ReasonForCancelId;
use Zend\InputFilter\InputFilter;
use Zend\Validator\NotEmpty;

class CancelMotTestInputFilter extends InputFilter
{
    const FIELD_REASON_FOR_CANCEL_ID = "reasonForCancelId";
    const FIELD_CANCEL_COMMENT = "cancelComment";

    const MESSAGE_REASON_FOR_CANCEL_ID_REQUIRED = "Reason for cancelling the test is required";
    const MESSAGE_CANCEL_COMMENT_REQUIRED = "Description is required";

    public function __construct()
    {
        $reasonForCancelId = array(
            'name' => self::FIELD_REASON_FOR_CANCEL_ID,
            'required' => true,
            'validators' => array(
                array(
                    'name' => 'not_empty',
                    'options' => array(
                        'messages' => array(
                            NotEmpty::IS_EMPTY => self::MESSAGE_REASON_FOR_CANCEL_ID_REQUIRED
                        ),
                    ),
                ),
            ),
        );

        $this->add($reasonForCancelId);

        $cancelComment = array(
            'name' => self::FIELD_CANCEL_COMMENT,
            'required' => true,
            'validators' => array(
                array(
                    'name' => 'not_empty',
                    'options' => array(
                        'messages' => array(
                            NotEmpty::IS_EMPTY => self::MESSAGE_CANCEL_COMMENT_REQUIRED
                        ),
                    ),
                ),
            ),
        );

        $this->add($cancelComment);
    }

    /**
     * @return bool
     */
    public function isCommentFieldRequired()
    {
        if (!$this->isRequiredValueMissing(self::FIELD_REASON_FOR_CANCEL_ID, $this->data)) {
            if ($this->data[self::FIELD_REASON_FOR_CANCEL_ID] == ReasonForCancelId::DANGR) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $fieldName
     * @param $data
     * @return bool
     */
    private function isRequiredValueMissing($fieldName, $data)
    {
        return !array_key_exists($fieldName, $data) || empty($data[$fieldName]);
    }
}