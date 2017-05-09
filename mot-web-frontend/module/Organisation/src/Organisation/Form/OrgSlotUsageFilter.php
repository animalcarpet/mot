<?php
/**
 * @author Jakub Igla <jakub.igla@gmail.com>
 */

namespace Organisation\Form;

use DvsaCommon\Model\CustomDateSearch;
use Zend\Form\Annotation;

/**
 * @Annotation\Hydrator("Zend\Stdlib\Hydrator\ObjectProperty")
 * @Annotation\Attributes({"class":"form-vertical","id":"OrgSlotUsageFilter","method":"GET"})
 * @Annotation\Name("CustomDateSearch")
 */
class OrgSlotUsageFilter extends CustomDateSearch
{
    /**
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Name("searchText")
     * @Annotation\Attributes({"class":"form-control"})
     * @Annotation\AllowEmpty
     * @Annotation\Filter({"name":"StripTags"})
     * @Annotation\Options({"label":"Search", "description":"Search by VTS number, name or postcode, name or postcode"})
     */
    public $textSearch;
}
