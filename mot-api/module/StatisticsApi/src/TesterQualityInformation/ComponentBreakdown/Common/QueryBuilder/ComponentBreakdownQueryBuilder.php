<?php
namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Common\QueryBuilder;

class ComponentBreakdownQueryBuilder
{
    protected function getWhere()
    {
        return '';
    }

    protected function getJoin()
    {
        return '';
    }

    protected function getUseIndex()
    {
        return '';
    }

    public function getSql()
    {
        return "SELECT
          failedCount,
          category_names.id testItemCategoryId,
          category_names.name testItemCategoryName
        FROM (
               SELECT
                 COUNT(DISTINCT test.id)           failedCount,
                 tic.section_test_item_category_id testItemCategoryId
               FROM mot_test_current test
                    {$this->getUseIndex()}
                 -- /joining rfr categories
                 JOIN mot_test_current_rfr_map `rfr_map` ON `rfr_map`.mot_test_id = test.id
                 JOIN reason_for_rejection_type rfr_type ON rfr_type.id = rfr_map.rfr_type_id
                 JOIN reason_for_rejection rfr ON rfr.id = `rfr_map`.rfr_id
                 JOIN test_item_category tic ON rfr.test_item_category_id = tic.id
                 JOIN ti_category_language_content_map `language_map`
                   ON `language_map`.test_item_category_id = tic.section_test_item_category_id
                 JOIN language_type lt ON `language_map`.language_lookup_id = lt.id
                 -- joining rfr categories/
                 JOIN mot_test_type type ON type.id = test.mot_test_type_id
                 JOIN mot_test_status status ON status.id = test.status_id
                 LEFT JOIN vehicle ON (vehicle.id = test.vehicle_id) AND (vehicle.version = test.vehicle_version)
                 LEFT JOIN vehicle_hist ON (vehicle_hist.id = test.vehicle_id) AND (vehicle_hist.version = test.vehicle_version)
                 JOIN model_detail md ON md.id = COALESCE (vehicle.model_detail_id, vehicle_hist.model_detail_id)
                 JOIN vehicle_class class ON class.id = md.vehicle_class_id
                 JOIN vehicle_class_group class_group ON class_group.id = class.vehicle_class_group_id
                 LEFT JOIN mot_test_emergency_reason mter ON mter.id = test.id
                 {$this->getJoin()}
               WHERE test.completed_date BETWEEN :startDate AND :endDate
                 -- the only tests we take into account are failures
                 AND status.code = :failedStatusCode
                 AND (type.code = :normalTestCode OR type.code = :mysteryShopperTestCode)
                 AND lt.code = :languageTypeCode
                 AND emergency_log_id IS NULL
                 AND class_group.code = :groupCode
                 AND rfr_type.name NOT IN (:skippedRfrTypes)
                 {$this->getWhere()}
               GROUP BY testItemCategoryId
             ) x
          RIGHT JOIN (
                       SELECT
                         tic.id      `id`,
                         `language_map`.name `name`
                       FROM test_item_category tic
                         JOIN ti_category_language_content_map `language_map` ON tic.id = `language_map`.test_item_category_id
                         JOIN test_item_category_vehicle_class_map `item_class_map` ON `item_class_map`.test_item_category_id = tic.id
                         JOIN vehicle_class vc ON vc.id = `item_class_map`.vehicle_class_id
                         JOIN vehicle_class_group vcg ON vc.`vehicle_class_group_id` = vcg.id
                         JOIN language_type lt ON `language_map`.language_lookup_id = lt.id
                       WHERE lt.code = :languageTypeCode
                             AND tic.parent_test_item_category_id = 0
                             -- removing parent test item category and 'Items not tested' that is not appearing in frontend
                             AND tic.id NOT IN (0, 5800, 10000)
                             AND vcg.code = :groupCode
                       GROUP BY id
                     ) category_names ON testItemCategoryId = category_names.id
        GROUP BY category_names.id
        ORDER BY category_names.name";
    }
}
