<?php

namespace DvsaMotApi\Formatting;

use DvsaCommon\Enum\LanguageTypeCode;
use DvsaCommon\Enum\ReasonForRejectionTypeName;
use DvsaEntities\Entity\MotTestReasonForRejection;
use DvsaEntities\Entity\ReasonForRejection;
use DvsaEntities\Entity\TestItemSelector;

/**
 * DefectSentenceCaseConverter formats defect descriptions into sentence case with acronyms in upper case.
 */
class DefectSentenceCaseConverter
{
    const ACRONYM_PATTERN = '/[A-Z]{2,}/';
    const BETWEEN_QUOTES_PATTERN = '/["|\']{1}.*["|\']{1}/';

    /**
     * @var array
     */
    private static $SPECIAL_CASE_ACRONYMS = [
        'abs' => 'anti-lock braking system',
        'rbt' => 'roller brake tester',
        'srs' => 'supplementary restraint system',
        'vin' => 'vehicle identification number',
    ];

    /**
     * @param MotTestReasonForRejection $motTestRfr
     *
     * @return array
     */
    public function getDefectDetailsForTestResultsAndBasket(MotTestReasonForRejection $motTestRfr)
    {
        $defectDetails = [
            'name' => '',
            'testItemSelectorDescription' => '',
            'failureText' => '',
            'nameCy' => '',
            'testItemSelectorDescriptionCy' => '',
            'failureTextCy' => '',
        ];

        $defect = $motTestRfr->getReasonForRejection();
        $defectType = (string) $motTestRfr->getType();
        $categoryDetails = $this->getCategoryDetails($defect->getTestItemSelector());
        $defectDescriptions = $this->getDefectDetails($defect);

        $defectDetails['name'] = $categoryDetails['name'];
        $defectDetails['nameCy'] = $categoryDetails['nameCy'];
        $defectDetails['testItemSelectorDescription'] = $categoryDetails['description'];
        $defectDetails['testItemSelectorDescriptionCy'] = $categoryDetails['descriptionCy'];
        $defectDetails['failureText'] = ($defectType === ReasonForRejectionTypeName::ADVISORY)
            ? $defectDescriptions['advisoryText'] : $defectDescriptions['name'];
        $defectDetails['failureTextCy'] = ($defectType === ReasonForRejectionTypeName::ADVISORY)
            ? $defectDescriptions['advisoryTextCy'] : $defectDescriptions['nameCy'];

        // Formatting
        $defectDetails['testItemSelectorDescription'] = ucfirst(self::toFirstOccurrenceOfEachAcronymExpanded(strtolower($defectDetails['testItemSelectorDescription'])));
        $defectDetails['failureText'] = self::toFirstOccurrenceOfEachAcronymExpanded($defectDetails['failureText'], $defectDetails['testItemSelectorDescription']);
        if (empty($defectDetails['testItemSelectorDescription'])) {
            $defectDetails['failureText'] = ucfirst($defectDetails['failureText']);
        }

        return $defectDetails;
    }

    /**
     * @param ReasonForRejection $defect
     *
     * @return array
     */
    public function getDefectDetailsForAddADefect(ReasonForRejection $defect)
    {
        $defectDetails = [
            'description' => '',
            'advisoryText' => '',
        ];

        $categoryDetails = $this->getCategoryDetails($defect->getTestItemSelector());
        $defectDescriptions = $this->getDefectDetails($defect);

        $defectDetails['description'] = $categoryDetails['description'].' '.$defectDescriptions['name'];
        $defectDetails['advisoryText'] = $categoryDetails['description'].' '.$defectDescriptions['advisoryText'];

        // Formatting
        $defectDetails['description'] = ucfirst(self::toFirstOccurrenceOfEachAcronymExpanded(
            $defectDetails['description']));
        $defectDetails['advisoryText'] = ucfirst(self::toFirstOccurrenceOfEachAcronymExpanded(
            $defectDetails['advisoryText']));

        return $defectDetails;
    }

    /**
     * @param ReasonForRejection $defect
     *
     * @return array
     */
    public function getDefectDetailsForListAndSearch($defect)
    {
        $defectDetails = [
            'description' => '',
            'advisoryText' => '',
            'inspectionManualDescription' => '',
        ];

        $defectDescriptions = $this->getDefectDetails($defect);
        $defectDetails['description'] = $defectDescriptions['name'];
        $defectDetails['advisoryText'] = $defectDescriptions['advisoryText'];
        $defectDetails['inspectionManualDescription'] = $defectDescriptions['inspectionManualDescription'];

        $categoryDetails = $this->getCategoryDetails($defect->getTestItemSelector());
        $defectDetails['description'] = $categoryDetails['description'].' '.$defectDescriptions['name'];
        $defectDetails['description'] = ucfirst(self::toFirstOccurrenceOfEachAcronymExpanded($defectDetails['description']));
        $defectDetails['advisoryText'] = $categoryDetails['description'].' '.$defectDescriptions['advisoryText'];
        $defectDetails['advisoryText'] = ucfirst(self::toFirstOccurrenceOfEachAcronymExpanded($defectDetails['advisoryText']));
        $defectDetails['inspectionManualDescription'] = $defectDescriptions['inspectionManualDescription'];

        return $defectDetails;
    }

    /**
     * @param TestItemSelector $category
     *
     * @return array
     */
    public function getDetailsForDefectCategories(TestItemSelector $category)
    {
        $categoryDetails = $this->getCategoryDetails($category);

        // Formatting
        $categoryDetails['name'] = ucfirst(self::toAcronymsInUpperCase($categoryDetails['name']));

        return $categoryDetails;
    }

    /**
     * @param string $description
     * @param string $category
     *
     * @return string
     */
    private function toFirstOccurrenceOfEachAcronymExpanded($description, $category = null)
    {
        if (empty($description)) {
            return $description;
        }
        $acronymsExpanded = self::expandFirstOccurrenceOfEachAcronym($description, $category);
        $remainingAcronymsToUpper = self::toAcronymsInUpperCase($acronymsExpanded);

        return $remainingAcronymsToUpper;
    }

    /**
     * @param string $description
     *
     * @return string
     */
    private function toAcronymsInUpperCase($description)
    {
        if (empty($description)) {
            return $description;
        }
        $words = $wordsArray = preg_split('/ /', $description);
        $acronymsInUpper = '';
        foreach ($words as $word) {
            // Ignore any uppercase acronyms
            if (1 === preg_match(self::ACRONYM_PATTERN, $word)) {
                $acronymsInUpper .= ' '.$word;
                continue;
            }
            // Ignore any characters between quotes
            if (1 === preg_match(self::BETWEEN_QUOTES_PATTERN, $word)) {
                $acronymsInUpper .= ' '.$word;
                continue;
            }
            // Upper case our special acronyms
            if (in_array(strtolower($word), self::getListOfAcronyms())) {
                $acronymsInUpper .= ' '.strtoupper($word);
                continue;
            }
            $acronymsInUpper .= ' '.strtolower($word);
        }

        return trim($acronymsInUpper);
    }

    /**
     * @return array
     */
    private function getListOfAcronyms()
    {
        return array_keys(self::$SPECIAL_CASE_ACRONYMS);
    }

    /**
     * @param string $description
     * @param string $category
     *
     * @return array $words
     */
    private function expandFirstOccurrenceOfEachAcronym($description, $category = null)
    {
        $acronyms = self::$SPECIAL_CASE_ACRONYMS;

        // If category contains an expanded acronym already, remove that acronym from the list
        if ($category !== null) {
            foreach ($acronyms as $acronym => $acronymExpandedForm) {
                if (stripos($category, $acronymExpandedForm) !== false) {
                    unset($acronyms[$acronym]);
                }
            }
        }
        // If description contains an expanded acronym already, remove that acronym from the list
        foreach ($acronyms as $acronym => $acronymExpandedForm) {
            if (stripos($description, $acronymExpandedForm) !== false) {
                unset($acronyms[$acronym]);
            }
        }
        // Expand each remaining acronym in list only once in the description
        $words = $wordsArray = preg_split('/ /', $description);
        $result = '';
        for ($i = 0; $i < count($words); ++$i) {
            $wordInLowerCase = strtolower($words[$i]);
            if (in_array($wordInLowerCase, array_keys($acronyms))) {
                $acronymLongForm = $acronyms[$wordInLowerCase];
                unset($acronyms[$wordInLowerCase]);
                $words[$i] = $acronymLongForm;
            }
            $result .= ($words[$i]).' ';
        }

        return trim($result);
    }

    /**
     * @param TestItemSelector $category
     *
     * @return array
     */
    private function getCategoryDetails(TestItemSelector $category)
    {
        $categoryDetails = [
            'name' => '',
            'description' => '',
            'nameCy' => '',
            'descriptionCy' => '',
        ];

        if (null !== $category) {
            $descriptions = $category->getDescriptions();
            if (null !== $descriptions) {
                foreach ($category->getDescriptions() as $description) {
                    $language = $description->getLanguage();
                    if (null === $language) {
                        $categoryDetails['name'] = $description->getName();
                        $categoryDetails['description'] = $description->getDescription();
                        continue;
                    }
                    if ($language->getCode() === LanguageTypeCode::ENGLISH || null === $language->getCode()) {
                        $categoryDetails['name'] = $description->getName();
                        $categoryDetails['description'] = $description->getDescription();
                    } else if ($language->getCode() === LanguageTypeCode::WELSH) {
                        $categoryDetails['nameCy'] = $description->getName();
                        $categoryDetails['descriptionCy'] = $description->getDescription();
                    }
                }
            }
        }

        return $categoryDetails;
    }

    /**
     * @param ReasonForRejection $defect
     *
     * @return array
     */
    private function getDefectDetails($defect)
    {
        $defectDetails = [
            'name' => '',
            'advisoryText' => '',
            'inspectionManualDescription' => '',
            'nameCy' => '',
            'advisoryTextCy' => '',
            'inspectionManualDescriptionCy' => '',
        ];

        $defectDescriptions = $defect->getDescriptions();
        if (null !== $defectDescriptions) {
            foreach ($defectDescriptions as $defectDescription) {
                $defectLanguage = $defectDescription->getLanguage();
                if (null === $defectLanguage) {
                    $defectDetails['name'] = $defectDescription->getName();
                    $defectDetails['advisoryText'] = $defectDescription->getAdvisoryText();
                    $defectDetails['inspectionManualDescription'] = $defectDescription->getInspectionManualDescription();
                    continue;
                }
                if ($defectLanguage->getCode() === LanguageTypeCode::ENGLISH || null === $defectLanguage->getCode()) {
                    $defectDetails['name'] = $defectDescription->getName();
                    $defectDetails['advisoryText'] = $defectDescription->getAdvisoryText();
                    $defectDetails['inspectionManualDescription'] = $defectDescription->getInspectionManualDescription();
                } else if ($defectLanguage->getCode() === LanguageTypeCode::WELSH) {
                    $defectDetails['nameCy'] = $defectDescription->getName();
                    $defectDetails['advisoryTextCy'] = $defectDescription->getAdvisoryText();
                    $defectDetails['inspectionManualDescriptionCy'] = $defectDescription->getInspectionManualDescription();
                }
            }
        }

        return $defectDetails;
    }
}
