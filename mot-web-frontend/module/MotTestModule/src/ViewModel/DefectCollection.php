<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\ViewModel;

use Doctrine\Common\Collections\ArrayCollection;
use DvsaCommon\Utility\TypeCheck;
use DvsaCommon\Dto\ReasonForRejection\ReasonForRejectionDto;

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
                !$defectFromApi['isPrsFail'] && !$defectFromApi['isAdvisory'],
                $defectFromApi['deficiencyCategoryCode'],
                $defectFromApi['isPreEuDirective']
            );

            array_push($defects, $defect);
        }

        return new self($defects);
    }

    /**
     * @param ReasonForRejectionDto[] $result
     *
     *  @return DefectCollection
     */
    public static function fromSearchResult(array $result)
    {
        TypeCheck::assertCollectionOfClass($result, ReasonForRejectionDto::class);

        $defects = [];
        foreach ($result as $dto) {
            $defect = new Defect(
                $dto->getRfrId(),
                $dto->getTestItemSelectorId(),
                $dto->getDescription(),
                $dto->getTestItemSelectorName(),
                self::getAdvisoryText($dto),
                self::getInspectionManualReference($dto),
                $dto->getIsAdvisory(),
                $dto->getIsPrsFail(),
                self::isFailure($dto),
                $dto->getDeficiencyCategoryCode(),
                $dto->getIsPreEuDirective()
            );

            $defect->setInspectionManualReferenceUrl($dto->getInspectionManualReferenceUrl());

            $defects[] = $defect;
        }

        return new self($defects);
    }

    private static function getAdvisoryText(ReasonForRejectionDto $dto): string
    {
        $advisoryText = "";
        if (self::isDefectInNonComponentAdvisoriesCategory($dto->getTestItemSelectorId()) === false) {
            $advisoryText = $dto->getAdvisoryText();
        }

        return $advisoryText;
    }

    private static function getInspectionManualReference(ReasonForRejectionDto $dto): string
    {
        $inspectionManualReference = "";
        if (self::isDefectInNonComponentAdvisoriesCategory($dto->getTestItemSelectorId()) === false) {
            $inspectionManualReference = $dto->getInspectionManualReference();
        }

        return $inspectionManualReference;
    }

    private static function isFailure(ReasonForRejectionDto $dto): bool
    {
        return (!$dto->getIsPrsFail() && !$dto->getIsAdvisory());
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
