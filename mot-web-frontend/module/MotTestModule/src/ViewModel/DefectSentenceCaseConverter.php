<?php

namespace Dvsa\Mot\Frontend\MotTestModule\ViewModel;

class DefectSentenceCaseConverter
{
    const ACRONYM_PATTERN = '/[A-Z]{2,}/';

    /**
     * @param string $description
     *
     * @return string
     */
    public static function convert($description)
    {
        // Split the description into an array of words on ' '
        $wordsArray = preg_split('/ /', $description);

        // If the word is one of our special cases, strtoupper it
        if (in_array(strtolower($wordsArray[0]), self::getMalformedAcronyms())) {
            $wordsArray[0] = strtoupper($wordsArray[0]);
        }

        // If the first word is not an acronym, uppercase the first letter
        if (0 === preg_match(self::ACRONYM_PATTERN, $wordsArray[0])) {
            $wordsArray[0] = ucfirst($wordsArray[0]);
        }

        // We've already dealt with the first word
        $description = array_shift($wordsArray);

        // For each word in the description
        foreach ($wordsArray as $word) {
            // Ignore acronyms
            if (1 === preg_match(self::ACRONYM_PATTERN, $word)) {
                $description .= ' '.$word;
                continue;
            }

            // If the word is one of our special cases, strtoupper it
            if (in_array(strtolower($word), self::getMalformedAcronyms())) {
                $description .= ' '.strtoupper($word);
                continue;
            }

            // Lower case the word if it's not the first and not an acronym
            $word = strtolower($word);

            // Append the word to the description to be returned
            $description .= ' '.$word;
        }

        return $description;
    }

    private static function getMalformedAcronyms()
    {
        return [
            'abs',
            'srs',
            'vin',
        ];
    }
}
