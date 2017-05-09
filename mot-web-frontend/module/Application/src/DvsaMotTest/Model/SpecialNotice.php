<?php

namespace DvsaMotTest\Model;

use DvsaCommon\Date\DateUtils;
use DvsaCommon\Utility\ArrayUtils;
use Zend\Form\Annotation;

/**
 * @Annotation\Hydrator("Zend\Stdlib\Hydrator\ObjectProperty")
 * @Annotation\Name("SpecialNotice")
 */
class SpecialNotice
{
    /**
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true" })
     * @Annotation\Filter({"name":"StripTags"})
     * @Annotation\Validator({"name":"StringLength", "options":{"min":1, "max":100}})
     * @Annotation\Options({"label":"Subject title"})
     */
    public $noticeTitle;

    /**
     * @Annotation\Type("Zend\Form\Element\DateSelect")
     * @Annotation\Options({"label":"Internal publish date",
     *                      "format":"d-m-Y",
     *                      "hint":"Format: DD MM YYYY",
     *                      "day_attributes":{
     *                          "maxlength":"2"
     *                      },
     *                      "month_attributes":{
     *                          "maxlength":"2"
     *                      },
     *                      "year_attributes":{
     *                          "maxlength":"4"
     *                      }
     *                      })
     * @Annotation\Validator({"name":"DvsaMotTest\Form\Validator\SpecialNoticePublishDateValidator"})
     */
    public $internalPublishDate;

    /**
     * @Annotation\Type("Zend\Form\Element\DateSelect")
     * @Annotation\Options({"label":"External publish date",
     *                      "format":"d-m-Y",
     *                      "hint":"Format: DD MM YYYY",
     *                      "day_attributes":{
     *                          "maxlength":"2"
     *                      },
     *                      "month_attributes":{
     *                          "maxlength":"2"
     *                      },
     *                      "year_attributes":{
     *                          "maxlength":"4"
     *                      }
     *                      })
     * @Annotation\Validator({"name":"DvsaMotTest\Form\Validator\SpecialNoticePublishDateValidator"})
     */
    public $externalPublishDate;

    /**
     * @Annotation\Type("Zend\Form\Element\Number")
     * @Annotation\Required({"required":"true" })
     * @Annotation\Options({"label":"Acknowledgement period"})
     * @Annotation\Attributes({"min": 1, "max": 30, "step": 1})
     */
    public $acknowledgementPeriod;

    /**
     * @Annotation\Type("Zend\Form\Element\MultiCheckbox")
     * @Annotation\Options({"label_attributes":{ "class": "block-label label-clear"}})
     * @Annotation\Attributes({"options":{"TESTER-CLASS-1":"Class 1", "TESTER-CLASS-2":"Class 2",
     *                                    "TESTER-CLASS-3":"Class 3", "TESTER-CLASS-4":"Class 4",
     *                                    "TESTER-CLASS-5":"Class 5", "TESTER-CLASS-7":"Class 7",
     *                                    "DVSA":"DVSA Roles", "VTS":"VTS Roles"}})
     */
    public $targetRoles;

    /**
     * @Annotation\Type("Zend\Form\Element\Textarea")
     * @Annotation\Required({"required":"true" })
     * @Annotation\Filter({"name":"StripTags"})
     * @Annotation\Validator({"name":"StringLength", "options":{"min":1, "max":20000}})
     * @Annotation\Options({"label":"Subject message"})
     * @Annotation\Attributes({"rows":"19"})
     */
    public $noticeText;

    /**
     * @Annotation\Type("Zend\Form\Element\Submit")
     * @Annotation\Attributes({"value":"Preview"})
     */
    public $submit = 'Preview';

    public function exchangeArray($data)
    {
        $this->noticeTitle = ArrayUtils::tryGet($data, 'title');
        $this->noticeText = ArrayUtils::tryGet($data, 'noticeText');

        $this->targetRoles = ArrayUtils::tryGet($data, 'targetRoles', []);

        $this->internalPublishDate = ArrayUtils::tryGet($data, 'internalPublishDate');
        $this->externalPublishDate = ArrayUtils::tryGet($data, 'externalPublishDate');

        if ($this->externalPublishDate) {
            $dt = DateUtils::toDate($this->externalPublishDate);
            if (isset($data['expiryDate'])) {
                $expiryDate = DateUtils::toDate($data['expiryDate']);
                $this->acknowledgementPeriod = $expiryDate->diff($dt)->d;
            }
        }
    }

    public function getArrayCopy()
    {
        return [
            'title' => $this->noticeTitle,
            'noticeText' => $this->noticeText,
            'targetRoles' => $this->targetRoles,
            'internalPublishDate' => $this->internalPublishDate,
            'externalPublishDate' => $this->externalPublishDate,
            'acknowledgementPeriod' => $this->acknowledgementPeriod,
        ];
    }
}
