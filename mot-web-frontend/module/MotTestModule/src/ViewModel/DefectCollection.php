<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\ViewModel;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * A collection of Defect instances belonging to the same category.
 *
 * More specifically, these are defects that have not yet been added to a
 * vehicle. These are the defects that are displayed when you click on a
 * category in the browse journey that has no children categories.
 *
 * Previously known as Reasons For Rejection.
 */
class DefectCollection extends ArrayCollection
{
    /*
     * We don't want the testItemSelectorName for these, as it's just a
     * duplicate of the description.
     */
    const EMISSIONS_NOT_TESTED_CATEGORY_NAME = 'Emissions not tested';

    /*
     * Non-component advisories do not have inspection manual references
     * or advisory texts.
     */
    const NON_COMPONENT_ADVISORIES_ID = 10000;

    /**
     * DefectCollection constructor.
     *
     * @param Defect[] $defects
     */
    public function __construct(array $defects)
    {
        parent::__construct($defects);
    }

    /**
     * @param array $componentCategoriesFromApi
     *
     * @return DefectCollection
     */
    public static function fromDataFromApi(array $componentCategoriesFromApi)
    {
        $defectsFromApi = $componentCategoriesFromApi['reasonsForRejection'];

        $defects = [];

        foreach ($defectsFromApi as $defectFromApi) {

            $defectHeadingStartWithAcronymsExpanded = self::formatLastBreadcrumb($defectFromApi['testItemSelectorName']);

            $defect = new Defect(
                $defectFromApi['rfrId'],
                $defectFromApi['testItemSelectorId'],
                $defectHeadingStartWithAcronymsExpanded.' '.$defectFromApi['description'],
                '',
                !self::isDefectInNonComponentAdvisoriesCategory(
                    $defectFromApi['testItemSelectorId']
                ) ? $defectHeadingStartWithAcronymsExpanded.' '.$defectFromApi['advisoryText']
                  : '',
                !self::isDefectInNonComponentAdvisoriesCategory(
                    $componentCategoriesFromApi['testItemSelector']['name']
                ) ? $defectFromApi['inspectionManualReference']
                  : '',
                $defectFromApi['isAdvisory'],
                $defectFromApi['isPrsFail'],
                !$defectFromApi['isPrsFail'] && !$defectFromApi['isAdvisory']
            );

            array_push($defects, $defect);
        }

        return new self($defects);
    }

    /**
     * @param array $searchResults
     *
     * @return DefectCollection
     */
    public static function fromSearchResults(array $searchResults)
    {
        $defects = [];

        foreach ($searchResults['data']['reasonsForRejection'] as $searchResult) {

            $defectHeadingStartWithAcronymsExpanded = self::formatLastBreadcrumb($searchResult['testItemSelectorName']);
            $defectDescriptionWithAcronymsExpanded = self::formatDescription($searchResult['description']);

            $defect = new Defect(
                $searchResult['rfrId'],
                $searchResult['testItemSelectorId'],
                $defectDescriptionWithAcronymsExpanded,
                $searchResult['testItemSelectorName'],
                !self::isDefectInNonComponentAdvisoriesCategory(
                    $searchResult['testItemSelectorId']
                ) ? $defectHeadingStartWithAcronymsExpanded.' '.$searchResult['advisoryText']
                    : '',
                !self::isDefectInNonComponentAdvisoriesCategory(
                    $searchResult['testItemSelector']
                ) ? $searchResult['inspectionManualReference']
                    : '',
                $searchResult['isAdvisory'],
                $searchResult['isPrsFail'],
                !$searchResult['isPrsFail'] && !$searchResult['isAdvisory']
            );

            array_push($defects, $defect);
        }

        return new self($defects);
    }

    /**
     * @param string $breadcrumbs
     *
     * @return string
     */
    private static function formatLastBreadcrumb($breadcrumbs)
    {
        $defectBreadcrumbParts = explode('>', $breadcrumbs);
        $lastBreadcrumb = end($defectBreadcrumbParts);
        $lastBreadcrumbWithAcronymExpanded = DefectSentenceCaseConverter::convertWithFirstOccurrenceOfAcronymsExpanded($lastBreadcrumb);
        return ucfirst(trim($lastBreadcrumbWithAcronymExpanded, " "));
    }

    /**
     * @param string $description
     *
     * @return string
     */
    private static function formatDescription($description)
    {
        $descriptionWithAcronymsExpanded = DefectSentenceCaseConverter::convertWithFirstOccurrenceOfAcronymsExpanded($description);
        return ucfirst(trim($descriptionWithAcronymsExpanded, " "));
    }

    /**
     * @return Defect[]
     */
    public function getDefects()
    {
        return $this->getValues();
    }

    /**
     * @param int $testItemSelectorId
     *
     * @return bool
     */
    public static function isDefectInNonComponentAdvisoriesCategory($testItemSelectorId)
    {
        return $testItemSelectorId === self::NON_COMPONENT_ADVISORIES_ID;
    }
}
