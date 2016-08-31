<?php
namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterMultiSite\QueryBuilder;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Common\QueryBuilder\TesterPerformanceQueryBuilder;

class TesterMultiSiteStatisticsQueryBuilder extends TesterPerformanceQueryBuilder
{
    protected $selectFields = "`class_group`.`code` `vehicleClassGroup`,
                               `vts`.`id` `siteId`,
                               `vts`.`name` `siteName`,
                               `address`.`address_line_1` `siteAddressLine1`,
                               `address`.`address_line_2` `siteAddressLine2`,
                               `address`.`address_line_4` `siteAddressLine4`,
                               `address`.`postcode` `sitePostcode`,
                               `address`.`town` `siteTown`,
                               `address`.`country` `siteCountry`,";

    protected $index = "USE INDEX (`mot_test_person_id_started_date_completed_date_idx`)";

    protected $where = "AND `test`.`person_id` = :testerId ";

    protected $join = "JOIN `site_contact_detail_map` ON `site_contact_detail_map`.`site_id` = `vts`.`id`
                       JOIN `contact_detail` ON `site_contact_detail_map`.`contact_detail_id` = `contact_detail`.`id`
                       JOIN `address` ON `contact_detail`.`address_id` = `address`.`id`";

    protected $groupBy = "GROUP BY `vts`.`id`, `class_group`.`code`
                          ORDER BY `totalCount` DESC";
}