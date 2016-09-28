<?php

namespace DvsaCommon\Formatting;

use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Enum\LanguageTypeCode;
use DvsaCommon\Enum\ReasonForRejectionTypeName;
use DvsaEntities\Entity\ReasonForRejection;
use DvsaEntities\Entity\TestItemSelector;
use DvsaFeature\FeatureToggles;

/**
 * DefectSentenceCaseConverter formats defect descriptions into sentence case with acronyms in upper case.
 */
class DefectSentenceCaseConverter
{
    const ACRONYM_PATTERN = '/[A-Z]{2,}/';
    const BETWEEN_QUOTES_PATTERN = '/["|\']{1}.*["|\']{1}/';

    /**
     * @var FeatureToggles
     */
    private $featureToggles;

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
     * DefectSentenceCaseConverter constructor.
     *
     * @param FeatureToggles $featureToggles
     */
    public function __construct(FeatureToggles $featureToggles)
    {
        $this->featureToggles = $featureToggles;
    }

    /**
     * @param ReasonForRejection $rfr
     * @param string             $type
     *
     * @return array
     */
    public function formatRfrDescriptionsForTestResultsAndBasket(ReasonForRejection $rfr, $type)
    {
        $descriptions = [];

        $rfrDescriptions = $rfr->getDescriptions();
        if (null !== $rfrDescriptions) {
            foreach ($rfrDescriptions as $rfrDescription) {
                $rfrDescriptionLanguage = $rfrDescription->getLanguage();
                if (null !== $rfrDescriptionLanguage) {
                    $rfrDescriptionLanguageCode = $rfrDescriptionLanguage->getCode();
                    if ($rfrDescriptionLanguageCode === LanguageTypeCode::ENGLISH || $rfrDescriptionLanguageCode === null) {
                        $categoryDescription = '';
                        $rfrCategory = $rfr->getTestItemSelector();
                        if (null !== $rfrCategory) {
                            foreach ($rfrCategory->getDescriptions() as $rfrCategoryDescription) {
                                $categoryDescriptionLanguage = $rfrCategoryDescription->getLanguage();
                                if (null !== $categoryDescriptionLanguage) {
                                    if ($categoryDescriptionLanguage->getCode() === LanguageTypeCode::ENGLISH || null === $categoryDescriptionLanguage->getCode()) {
                                        $categoryDescription = $rfrCategoryDescription->getDescription();
                                    }
                                }
                            }
                            $descriptions['testItemSelectorDescription'] = $categoryDescription;
                            if ($type === ReasonForRejectionTypeName::ADVISORY) {
                                $descriptions['failureText'] = $rfrDescription->getAdvisoryText();
                            } else {
                                $descriptions['failureText'] = $rfrDescription->getName();
                            }
                        }
                    }
                }

                // Format text
                if ($this->isTestResultEntryImprovemenetsEnabled()) {
                    $descriptions['testItemSelectorDescription'] = self::toFirstLetterUpper(self::toFirstOccurrenceOfEachAcronymExpanded(strtolower($descriptions['testItemSelectorDescription'])));
                    $descriptions['failureText'] = self::toFirstOccurrenceOfEachAcronymExpanded($descriptions['failureText'], $descriptions['testItemSelectorDescription']);
                    if ($descriptions['testItemSelectorDescription'] === '') {
                        $descriptions['failureText'] = self::toFirstLetterUpper($descriptions['failureText']);
                    }
                }
            }

            $descriptions += $this->fetchLocalizedRrfNames($rfr);
        }

        return $descriptions;
    }

    /**
     * @param ReasonForRejection $rfr
     *
     * @return array
     */
    public function formatRfrDescriptionsForAddADefect(ReasonForRejection $rfr)
    {
        $descriptions = [];

        // Get category description
        $categoryDescription = $this->getCategoryDescription($rfr);

        // Get rfr description and advisory text
        $descriptionText = "";
        $advisoryText = "";
        $rfrDescriptions = $rfr->getDescriptions();
        if (null !== $rfrDescriptions) {
            foreach ($rfrDescriptions as $description) {
                $rfrDescriptionLanguage = $description->getLanguage();
                if (null !== $rfrDescriptionLanguage) {
                    $languageCode = $rfrDescriptionLanguage->getCode();
                    if ($languageCode === LanguageTypeCode::ENGLISH || $languageCode === null) {
                        $descriptionText = $description->getEnglishOfReasonForRejectionDescription();
                        $advisoryText = $description->getEnglishOfReasonForRejectionAdvisoryText();
                    }
                }
            }
        }
        $concatenatedDescription = $categoryDescription . ' ' . $descriptionText;
        $concatenatedAdvisoryText = $categoryDescription . ' ' . $advisoryText;

        // Format text
        if ($this->isTestResultEntryImprovemenetsEnabled()) {
            $descriptions['description'] = self::toFirstLetterUpper(self::toFirstOccurrenceOfEachAcronymExpanded(
                $concatenatedDescription));
            $descriptions['advisoryText'] = self::toFirstLetterUpper(self::toFirstOccurrenceOfEachAcronymExpanded(
                $concatenatedAdvisoryText));
        }

        return $descriptions;
    }

    /**
     * @param ReasonForRejection $rfr
     *
     * @return array
     */
    public function formatRfrDescriptionsForDefectsAndSearchForADefect($rfr)
    {
        $descriptions = [];

        $categoryDescriptionInEnglish = $this->getCategoryDescription($rfr);

        $rfrDescriptions = $rfr->getDescriptions();
        if (null !== $rfrDescriptions) {
            foreach ($rfrDescriptions as $rfrDescription) {
                if ($rfrDescription->getLanguage() === null
                    || $rfrDescription->getLanguage()->getCode() === LanguageTypeCode::ENGLISH) {
                    $descriptions['description'] = self::buildDescriptionOrAdvisoryText(
                        $categoryDescriptionInEnglish,
                        $rfrDescription->getName());
                    $descriptions['advisoryText'] = self::buildDescriptionOrAdvisoryText(
                        $categoryDescriptionInEnglish,
                        $rfrDescription->getAdvisoryText());
                    $descriptions['inspectionManualDescription'] = $rfrDescription->getInspectionManualDescription();
                }
            }
        }

        return $descriptions;
    }

    /**
     * @param array            $defectDescriptions
     * @param TestItemSelector $testItemSelector
     *
     * @return array
     */
    public function formatTisDescriptionsForDefectCategories(array $defectDescriptions,
                                                             TestItemSelector $testItemSelector)
    {
        $categoryDescriptions = $testItemSelector->getDescriptions();
        if (null !== $categoryDescriptions) {
            foreach ($categoryDescriptions as $description) {
                $language = $description->getLanguage();
                if (null !== $language) {
                    if ($language->getCode() === LanguageTypeCode::ENGLISH) {
                        $defectDescriptions['name'] = $description->getName();
                        $defectDescriptions['description'] = $description->getDescription();

                        // Format text
                        if ($this->isTestResultEntryImprovemenetsEnabled()) {
                            $defectDescriptions['name'] = self::toFirstLetterUpper(self::toAcronymsInUpperCase($defectDescriptions['name']));
                        }
                    }
                }
            }
        }

        return $defectDescriptions;
    }

    /**
     * @return boolean
     */
    private function isTestResultEntryImprovemenetsEnabled()
    {
        return $this->featureToggles->isEnabled(FeatureToggle::TEST_RESULT_ENTRY_IMPROVEMENTS);
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
        $acronymsInUpper = "";
        foreach ($words as $word) {
            // Ignore any uppercase acronyms
            if (1 === preg_match(self::ACRONYM_PATTERN, $word)) {
                $acronymsInUpper .= ' ' . $word;
                continue;
            }
            // Ignore any characters between quotes
            if (1 === preg_match(self::BETWEEN_QUOTES_PATTERN, $word)) {
                $acronymsInUpper .= ' ' . $word;
                continue;
            }
            // Upper case our special acronyms
            if (in_array(strtolower($word), self::getListOfAcronyms())) {
                $acronymsInUpper .= ' ' . strtoupper($word);
                continue;
            }
            $acronymsInUpper .= ' ' . strtolower($word);
        }

        return trim($acronymsInUpper);
    }

    /**
     * @param String $category
     * @param String $descriptionOrAdvisoryText
     *
     * @return String
     */
    private function buildDescriptionOrAdvisoryText($category, $descriptionOrAdvisoryText)
    {
        if (null === $category) {
            $category = "";
        }
        if (null === $descriptionOrAdvisoryText) {
            $descriptionOrAdvisoryText = "";
        }

        if (true !== $this->isTestResultEntryImprovemenetsEnabled()) {
            return $descriptionOrAdvisoryText;
        }

        // Format text
        $descriptionOrAdvisoryText = $category . " " . $descriptionOrAdvisoryText;
        $descriptionOrAdvisoryText = self::toFirstOccurrenceOfEachAcronymExpanded($descriptionOrAdvisoryText);

        return self::toFirstLetterUpper($descriptionOrAdvisoryText);
    }

    /**
     * @return array
     */
    private function getListOfAcronyms()
    {
        return array_keys(self::$SPECIAL_CASE_ACRONYMS);
    }

    /**
     * @param string
     *
     * @return string
     */
    private function toFirstLetterUpper($str)
    {
        return ucfirst($str);
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
                };
            }
        }
        // If description contains an expanded acronym already, remove that acronym from the list
        foreach ($acronyms as $acronym => $acronymExpandedForm) {
            if (stripos($description, $acronymExpandedForm) !== false) {
                unset($acronyms[$acronym]);
            };
        }
        // Expand each remaining acronym in list only once in the description
        $words = $wordsArray = preg_split('/ /', $description);
        $result = "";
        for ($i = 0; $i < count($words); $i++) {
            $wordInLowerCase = strtolower($words[$i]);
            if (in_array($wordInLowerCase, array_keys($acronyms))) {
                $acronymLongForm = $acronyms[$wordInLowerCase];
                unset($acronyms[$wordInLowerCase]);
                $words[$i] = $acronymLongForm;
            }
            $result .= ($words[$i]) . ' ';
        }

        return trim($result);
    }

    /**
     * @param ReasonForRejection $rfr
     *
     * @return string
     */
    private function getCategoryDescription($rfr)
    {
        $categoryDescriptionInEnglish = "";
        $rfrCategory = $rfr->getTestItemSelector();
        if (null !== $rfrCategory) {
            $rfrCategoryDescriptions = $rfrCategory->getDescriptions();
            if (null !== $rfrCategoryDescriptions) {
                foreach ($rfrCategoryDescriptions as $description) {
                    $language = $description->getLanguage();
                    if (null !== $language) {
                        $languageCode = $language->getCode();
                        if ($languageCode === LanguageTypeCode::ENGLISH || $languageCode === null) {
                            $categoryDescriptionInEnglish = $description->getDescription();
                        }
                    }
                }
            }
        }

        return $categoryDescriptionInEnglish;
    }

    /**
     * @param \DvsaEntities\Entity\ReasonForRejection $rfr
     *
     * @return array
     */
    private function fetchLocalizedRrfNames(ReasonForRejection $rfr)
    {
        $rfrNames = [];

        foreach ($rfr->getTestItemSelector()->getDescriptions() as $rfrCategoryDescription) {
            if ($rfrCategoryDescription->getLanguage()->getCode() === LanguageTypeCode::ENGLISH) {
                $rfrNames['name'] = $rfrCategoryDescription->getName();
            } elseif ($rfrCategoryDescription->getLanguage()->getCode() === LanguageTypeCode::WELSH) {
                $rfrNames['nameCy'] = $rfrCategoryDescription->getName();
            }
        }

        return $rfrNames;
    }
}
