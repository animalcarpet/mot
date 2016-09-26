<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\ViewModel;

use Doctrine\Common\Collections\ArrayCollection;
use DvsaMotApi\Helper\DefectDescriptionsHelper;

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

            $defect = new Defect(
                $defectFromApi['rfrId'],
                $defectFromApi['testItemSelectorId'],
                $defectFromApi['description'],
                '',
                !self::isDefectInNonComponentAdvisoriesCategory(
                    $defectFromApi['testItemSelectorId']
                ) ? $defectFromApi['advisoryText']
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

            $defect = new Defect(
                $searchResult['rfrId'],
                $searchResult['testItemSelectorId'],
                $searchResult['description'],
                $searchResult['testItemSelectorName'],
                !self::isDefectInNonComponentAdvisoriesCategory(
                    $searchResult['testItemSelectorId']
                ) ? $searchResult['advisoryText']
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
