<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use DvsaCommon\Enum\LanguageTypeCode;
use DvsaEntities\Entity\Language;
use DvsaEntities\Entity\TestItemCategoryDescription;
use DvsaEntities\Entity\TestItemSelector;

/**
 * A repository for Test Item Category related functionality
 *
 * @codeCoverageIgnore
 */
class TestItemCategoryRepository extends EntityRepository
{
    /** @var EntityManager $entityManager */
    private $entityManager;

    const SPECIAL_PROCESSING_SELECTOR_FORMAT = '%(sp)';

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $vehicleClass
     *
     * @return TestItemSelector[]
     */
    public function findByVehicleClass($vehicleClass)
    {
        return $this->entityManager
            ->createQuery(
                '
                SELECT t FROM DvsaEntities\Entity\TestItemSelector t
                JOIN t.vehicleClasses vc
                JOIN t.descriptions d
                JOIN d.language l
                WHERE vc.code = ?1
                AND t.parentTestItemSelectorId = 0
                AND t.id != 0
                AND l.code = ?2
                ORDER BY d.name ASC
                '
            )
            ->setParameter(1, $vehicleClass)
            ->setParameter(2, LanguageTypeCode::ENGLISH)
            ->getResult();
    }

    /**
     * @param int $id
     * @param string $vehicleClass
     *
     * @return TestItemSelector[]
     */
    public function findByIdAndVehicleClass($id, $vehicleClass)
    {
        return $this->entityManager
            ->createQuery(
                '
                SELECT t
                FROM DvsaEntities\Entity\TestItemSelector t
                JOIN t.vehicleClasses vc
                WHERE t.id = ?1
                AND vc.code = ?2
                '
            )
            ->setParameter(1, $id)
            ->setParameter(2, $vehicleClass)
            ->getResult();
    }

    /**
     * @param int $parentId
     * @param string $vehicleClass
     *
     * @return TestItemSelector[]
     */
    public function findByParentIdAndVehicleClass($parentId, $vehicleClass, $audience) {
        $sql = <<<SQL
            SELECT
            cat.id, cat.`parent_test_item_category_id`, cat.`section_test_item_category_id`,
            cat_l.id AS desc_id, cat_l.name, cat_l_lang.id AS lang_id, cat_l_lang.code AS lang_code, child.id AS child_id
            FROM `test_item_category` AS cat
            LEFT JOIN reason_for_rejection rfr ON (rfr.`test_item_category_id` = cat.id AND (rfr.audience = :audience OR rfr.audience = 'b'))
            LEFT JOIN test_item_category child ON child.`parent_test_item_category_id` = cat.id
            JOIN `test_item_category_vehicle_class_map` cat_vclass ON cat_vclass.`test_item_category_id` = cat.id
            JOIN `vehicle_class` vclass ON cat_vclass.`vehicle_class_id` = vclass.id
            JOIN ti_category_language_content_map cat_l ON cat_l.test_item_category_id = cat.`id`
            JOIN language_type cat_l_lang ON cat_l_lang.id = cat_l.`language_lookup_id`
            WHERE cat.`parent_test_item_category_id` = :parentId
            AND (rfr.`end_date` IS NULL OR rfr.`end_date` > CURRENT_DATE)
            AND vclass.code = :vehicleClassCode
            AND cat_l_lang.code = :languageCode
            AND cat_l.name NOT LIKE :specialProc
            AND cat.id != 0
            GROUP BY id
            HAVING (count(rfr.id) > 0 OR child_id IS NOT NULL )
            ORDER BY cat_l.name
SQL;

        return $this->entityManager
            ->createNativeQuery($sql, $this->createCategoryResultSetMapping())
            ->setParameter('audience', $audience)
            ->setParameter('parentId', $parentId)
            ->setParameter('vehicleClassCode', $vehicleClass)
            ->setParameter('specialProc', self::SPECIAL_PROCESSING_SELECTOR_FORMAT)
            ->setParameter('languageCode', LanguageTypeCode::ENGLISH)
            ->getResult();
    }

    private static function createCategoryResultSetMapping()
    {
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(TestItemSelector::class, 'ti');
        $rsm->addFieldResult('ti', 'id', 'id');
        $rsm->addFieldResult('ti', 'parent_test_item_category_id', 'parentTestItemSelectorId');
        $rsm->addFieldResult('ti', 'section_test_item_category_id', 'sectionTestItemSelectorId');
        $rsm->addJoinedEntityResult(TestItemCategoryDescription::class, 'desc', 'ti', 'descriptions');
        $rsm->addFieldResult('desc', 'desc_id', 'id');
        $rsm->addFieldResult('desc', 'name', 'name');
        $rsm->addJoinedEntityResult(Language::class, 'lang', 'desc', 'language');
        $rsm->addFieldResult('lang', 'lang_id', 'id');
        $rsm->addFieldResult('lang', 'lang_code', 'code');

        return $rsm;
    }
}
