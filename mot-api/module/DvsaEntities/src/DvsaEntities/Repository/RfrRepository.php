<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use DvsaCommon\Enum\LanguageTypeCode;
use DvsaEntities\Entity\ReasonForRejectionDescription;
use DvsaEntities\Entity\ReasonForRejection;
use DvsaEntities\Entity\TestItemSelector;

/**
 * A repository for Reasons For Rejection related functionality
 *
 * @codeCoverageIgnore
 */
class RfrRepository
{

    /** @var EntityManager */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param string $vehicleClassCode see \DvsaCommon\Enum\VehicleClassCode
     * @return array
     */
    public function getCurrentTestItemCategoriesWithRfrsByVehicleCriteria($vehicleClassCode)
    {
        $data = $this->em
            ->createNativeQuery('
                    SELECT
                      DISTINCT categoryDescription.name as name,
                      parentCategoryDescription.name as parentName
                    FROM reason_for_rejection rfr
                    JOIN test_item_category category ON rfr.test_item_category_id = category.id
                    JOIN test_item_category_vehicle_class_map classMap ON category.id = classMap.test_item_category_id
                    JOIN vehicle_class vClass ON classMap.vehicle_class_id = vClass.id
                    JOIN test_item_category parentCategory ON category.parent_test_item_category_id = parentCategory.id
                    JOIN `ti_category_language_content_map` categoryDescription
                 	  ON categoryDescription.`test_item_category_id` = category.id
                    JOIN `language_type` categoryLanguage ON categoryLanguage.id = categoryDescription.`language_lookup_id`
                    JOIN `ti_category_language_content_map` parentCategoryDescription
                 	  ON parentCategoryDescription.`test_item_category_id` = parentCategory.id
                    JOIN `language_type` parentCategoryLanguage
                 	  ON parentCategoryDescription.`language_lookup_id`= parentCategoryLanguage.id
                    WHERE vClass.code = :vehicleClassCode
                    AND (rfr.end_date is null or rfr.end_date > CURRENT_DATE)
                    AND categoryLanguage.code = :languageCode
                    AND parentCategoryLanguage.code = :languageCode
                ',
                (new ResultSetMapping())
                    ->addScalarResult('name', 'name')
                    ->addScalarResult('parentName', 'parentName')
            )
            ->setParameter('vehicleClassCode', $vehicleClassCode)
            ->setParameter('languageCode', LanguageTypeCode::ENGLISH)
            ->getResult();

        return $data;
    }

    /**
     * Find current RFRs.
     * @param int    $id
     * @param string $role
     * @param string $vehicleClass
     *
     * @return ReasonForRejection[]
     */
    public function findByIdAndVehicleClassForUserRole($id, $vehicleClass, $role)
    {
        return $this->em
            ->createQuery(
                '
                SELECT tRfr
                FROM ' . ReasonForRejection::class . ' tRfr
                JOIN tRfr.vehicleClasses vc
                WHERE tRfr.testItemSelectorId = ?1
                    AND vc.code = ?2
                    AND (tRfr.audience = ?3 OR tRfr.audience = \'b\')
                    AND (tRfr.endDate is null or tRfr.endDate > CURRENT_DATE())
                    AND tRfr.specProc = 0
                '
            )
            ->setParameter(1, $id)
            ->setParameter(2, $vehicleClass)
            ->setParameter(3, $role)
            ->getResult();
    }

    /**
     * @param string $searchString
     * @param string $vehicleClass
     * @param string $role
     * @param int    $start
     * @param int    $end
     *
     * @return ReasonForRejection[]
     */
    public function findBySearchQuery(
        $searchString,
        $vehicleClass,
        $role,
        $start,
        $end
    ) {
        $rsm = $this->getSearchResultSetMapping();

        $booleanSearch = str_replace(
            ['+', '<', '>', '&', '-', '@', '(', ')', '~', '*', '"'],
            ' ',
            $searchString
        );

        $likeSearchParam = "$searchString%";

        return $this->em
            ->createNativeQuery(
                '
                SELECT
                    rfr.id as rfr_id,
                    rfr.test_item_category_id,
                    rfr.test_item_selector_name,
                    rfr.inspection_manual_reference,
                    rfr.minor_item,
                    rfr.location_marker,
                    rfr.qt_marker,
                    rfr.note,
                    rfr.manual,
                    rfr.spec_proc,
                    rfr.is_advisory,
                    rfr.is_prs_fail,
                    rfr.can_be_dangerous,
                    rfr.audience,
                    tis.id testItemSelect_id,
                    tis.parent_test_item_category_id,
                    tis.section_test_item_category_id,
                    rfl.id as rfl_id,
                    rfl.name,
                    rfl.advisory_text,
                    rfl.inspection_manual_description,
                MATCH (rfl.name, rfl.test_item_selector_name) AGAINST (:searchString IN BOOLEAN MODE) AS rank
                FROM reason_for_rejection rfr
                JOIN rfr_vehicle_class_map vc ON rfr.id = vc.rfr_id
                JOIN vehicle_class vclass ON vc.vehicle_class_id = vclass.id
                JOIN rfr_language_content_map rfl on rfl.rfr_id = rfr.id
                JOIN language_type lang on rfl.language_type_id = lang.id
                JOIN test_item_category tis on rfr.test_item_category_id = tis.id
                WHERE vclass.code = :vehicleClass
                    AND (rfr.audience = :role OR rfr.audience = \'b\')
                    AND (
                        MATCH (rfl.name, rfl.test_item_selector_name) AGAINST (:searchString IN BOOLEAN MODE)
                        OR rfr.inspection_manual_reference LIKE :likeSearchParam
                        OR rfr.id LIKE :likeSearchParam
                    )
                    AND rfr.spec_proc = 0
                    AND (rfr.end_date IS NULL OR rfr.end_date > CURRENT_DATE)
                    AND lang.code = :languageCode
                ORDER BY rank DESC, rfr.inspection_manual_reference ASC LIMIT :limitStart, :limitEnd
                ',
                $rsm
            )
            ->setParameter('searchString', $booleanSearch)
            ->setParameter('vehicleClass', $vehicleClass)
            ->setParameter('role', $role)
            ->setParameter('likeSearchParam', $likeSearchParam)
            ->setParameter('limitStart', $start)
            ->setParameter('limitEnd', $end)
            ->setParameter('languageCode', LanguageTypeCode::ENGLISH)
            ->getResult();
    }

    /**
     * @return ResultSetMapping
     */
    private static function getSearchResultSetMapping()
    {
        $rsm = new ResultSetMapping;
        $rsm->addEntityResult(ReasonForRejection::class, 'rfr');
        $rsm->addFieldResult('rfr', 'rfr_id', 'rfrId');
        $rsm->addFieldResult('rfr', 'test_item_category_id', 'testItemSelectorId');
        $rsm->addFieldResult('rfr', 'test_item_selector_name', 'testItemSelectorName');
        $rsm->addFieldResult('rfr', 'inspection_manual_reference', 'inspectionManualReference');
        $rsm->addFieldResult('rfr', 'minor_item', 'minorItem');
        $rsm->addFieldResult('rfr', 'location_marker', 'locationMarker');
        $rsm->addFieldResult('rfr', 'qt_marker', 'qtMarker');
        $rsm->addFieldResult('rfr', 'note', 'note');
        $rsm->addFieldResult('rfr', 'manual', 'manual');
        $rsm->addFieldResult('rfr', 'spec_proc', 'specProc');
        $rsm->addFieldResult('rfr', 'is_advisory', 'isAdvisory');
        $rsm->addFieldResult('rfr', 'is_prs_fail', 'isPrsFail');
        $rsm->addFieldResult('rfr', 'can_be_dangerous', 'canBeDangerous');
        $rsm->addFieldResult('rfr', 'audience', 'audience');

        $rsm->addJoinedEntityResult(ReasonForRejectionDescription::class, 'rfl', 'rfr', 'descriptions');
        $rsm->addFieldResult('rfl', 'rfl_id', 'id');
        $rsm->addFieldResult('rfl', 'name', 'name');
        $rsm->addFieldResult('rfl', 'advisory_text', 'advisoryText');
        $rsm->addFieldResult('rfl', 'inspection_manual_description', 'inspectionManualDescription');

        $rsm->addJoinedEntityResult(TestItemSelector::class, 'tis', 'rfr', 'testItemSelector');
        $rsm->addFieldResult('tis', 'testItemSelect_id', 'id');

        return $rsm;
    }
}
