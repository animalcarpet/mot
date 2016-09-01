<?php

namespace Dvsa\Mot\Frontend\MotTestModule\ViewModel;

class DefectSentenceCaseConverter
{
    const ACRONYM_PATTERN = '/[A-Z]{2,}/';
    const BETWEEN_QUOTES_PATTERN = '/["|\']{1}.*["|\']{1}/';

    /**
     * @var array
     */
    private static $SPECIAL_CASE_ACRONYMS = [
                                'abs' => 'anti-lock brake system',
                                'rbt' => 'roller brake tester',
                                'srs' => 'supplementary restraint system',
                                'vin' => 'vehicle identification number'
                            ];

    /**
     * @param string $descriptionFromApi
     *
     * @return string
     */
    public static function convert($descriptionFromApi)
    {
        $words = self::createWordArray($descriptionFromApi);
        $description = self::createDescriptionFromWords($words);
        return $description;
    }

    /**
     * @param string $descriptionFromApi
     *
     * @return string
     */
    public static function convertWithFirstOccurrenceOfAcronymsExpanded($descriptionFromApi)
    {
        $words = self::createWordArray($descriptionFromApi);
        $words = self::expandFirstOccurrenceOfEachAcronym($words);
        $description = self::createDescriptionFromWords($words);
        return $description;
    }

    /**
     * @param string $description
     *
     * @return array
     */
    private static function createWordArray($description)
    {
        $wordsArray = preg_split('/ /', $description);
        return $wordsArray;
    }

    /**
     * @param array $words
     *
     * @return string
     */
    private static function createDescriptionFromWords($words)
    {
        $description = "";
        foreach ($words as $word) {
            // Ignore any uppercase acronyms
            if (1 === preg_match(self::ACRONYM_PATTERN, $word)) {
                $description .= ' ' . $word;
                continue;
            }
            // Ignore any characters between quotes
            if (1 === preg_match(self::BETWEEN_QUOTES_PATTERN, $word)) {
                $description .= ' ' . $word;
                continue;
            }
            // Upper case our special acronyms
            if (in_array(strtolower($word), self::getListOfAcronyms())) {
                $description .= ' ' . strtoupper($word);
                continue;
            }
            $description .= ' ' . strtolower($word);
        }
        return ucfirst(trim($description));
    }

    /**
     * @return array
     */
    private static function getListOfAcronyms()
    {
        return array_keys(DefectSentenceCaseConverter::$SPECIAL_CASE_ACRONYMS);
    }

    /**
     * @param array $words
     *
     * @return array $words
     */
    private static function expandFirstOccurrenceOfEachAcronym($words)
    {
        $acronyms = DefectSentenceCaseConverter::$SPECIAL_CASE_ACRONYMS;

        for ($i = 0; $i < count($words); $i++) {
            $wordInLowerCase = strtolower($words[$i]);
            if (in_array($wordInLowerCase, array_keys($acronyms))) {
                $acronymLongForm = $acronyms[$wordInLowerCase];
                unset($acronyms[$wordInLowerCase]);
                $words[$i] = $acronymLongForm;
            }
        }
        return $words;
    }
}
