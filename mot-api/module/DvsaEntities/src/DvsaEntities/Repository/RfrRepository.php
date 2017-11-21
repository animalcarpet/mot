<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Enum\LanguageTypeCode;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\ReasonForRejection;
use DvsaCommon\Date\RfrCurrentDateFaker;
use DvsaMotApi\Service\TestItemSelectorService;

/**
 * A repository for Reasons For Rejection related functionality.
 *
 * @codeCoverageIgnore
 */
class RfrRepository extends AbstractMutableRepository
{
    /** @var EntityManager */
    private $em;

    /** @var  RfrCurrentDateFaker */
    private $rfrCurrentDateFaker;

    /** @var array */
    private $disabledRfrs = [];

    public function __construct(
        EntityManager $em,
        RfrCurrentDateFaker $rfrCurrentDateFaker,
        array $disabledRfrs)
    {
        $this->em = $em;
        $this->rfrCurrentDateFaker = $rfrCurrentDateFaker;
        $this->disabledRfrs = $disabledRfrs;
    }

    public function get($rfrId)
    {
        if (in_array($rfrId, $this->disabledRfrs)) {
            return null;
        }

        $reasonForRejection = $this->em
            ->createQuery(
                '
                SELECT tRfr
                FROM '.ReasonForRejection::class.' tRfr
                WHERE tRfr.rfrId = ?1
                '
            )
            ->setParameter(1, $rfrId)
            ->getResult();

        return $reasonForRejection[0];
    }

    /**
     * @param string $vehicleClassCode see \DvsaCommon\Enum\VehicleClassCode
     *
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
                    AND (rfr.end_date is null or rfr.end_date > :currentDate)
                    AND rfr.start_date <= :currentDate
                    AND categoryLanguage.code = :languageCode
                    AND parentCategoryLanguage.code = :languageCode
                ',
                (new ResultSetMapping())
                    ->addScalarResult('name', 'name')
                    ->addScalarResult('parentName', 'parentName')
            )
            ->setParameter('vehicleClassCode', $vehicleClassCode)
            ->setParameter('languageCode', LanguageTypeCode::ENGLISH)
            ->setParameter('currentDate', $this->rfrCurrentDateFaker->getCurrentDateTime())
            ->getResult();

        return $data;
    }

    /**
     * Find current RFRs.
     *
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
                SELECT tRfr, dc
                FROM '.ReasonForRejection::class.' tRfr
                JOIN tRfr.vehicleClasses vc
                JOIN tRfr.rfrDeficiencyCategory dc
                WHERE tRfr.testItemSelectorId = ?1
                    AND vc.code = ?2
                    AND (tRfr.audience = ?3 OR tRfr.audience = \'b\')
                    AND (tRfr.endDate is null or tRfr.endDate > :currentDate)
                    AND tRfr.startDate <= :currentDate
                    AND tRfr.specProc = 0
                '
            )
            ->setParameter(1, $id)
            ->setParameter(2, $vehicleClass)
            ->setParameter(3, $role)
            ->setParameter('currentDate', $this->rfrCurrentDateFaker->getCurrentDateTime())
            ->getResult();
    }

    public function findAll() :array
    {
        $stmt = $this->em->getConnection()->prepare(
                '
                SELECT 
                    rfr.id AS rfrId,
                    rfr.test_item_category_id AS testItemSelectorId,
                    rfl.test_item_selector_name AS testItemSelectorName,
                    rfr.inspection_manual_reference AS inspectionManualReference,
                    rfr.manual AS manual,
                    rfr.is_advisory AS isAdvisory,
                    rfr.is_prs_fail AS isPrsFail,
                    rfr.audience AS audience,
                    rfr.start_date AS startDate,
                    rfr.end_date AS endDate,
                    ticlm.name AS categoryName,
                    ticlm.description AS categoryDescription,
                    rfl.name AS description,
                    rfl.advisory_text AS advisoryText,
                    rfl.inspection_manual_description AS inspectionManualDescription,
                    rdc.id AS deficiencyCategoryId,
                    rdc.code AS deficiencyCategoryCode,
                    rdc.description AS deficiencyCategoryDescription,
                    GROUP_CONCAT(vclass.id) AS vehicleClasses
                FROM reason_for_rejection rfr
                
                JOIN rfr_vehicle_class_map vc ON rfr.id = vc.rfr_id
                JOIN vehicle_class vclass ON vc.vehicle_class_id = vclass.id
                JOIN rfr_language_content_map rfl ON rfl.rfr_id = rfr.id
                JOIN language_type lang ON rfl.language_type_id = lang.id
                JOIN test_item_category tis ON rfr.test_item_category_id = tis.id
                LEFT JOIN ti_category_language_content_map ticlm 
                    ON ticlm.test_item_category_id = tis.id
                    AND ticlm.language_lookup_id = (SELECT id FROM language_type WHERE code = :languageCode)
                JOIN rfr_deficiency_category rdc ON rdc.id = rfr.rfr_deficiency_category_id 

                WHERE rfr.spec_proc = 0
                    AND (rfr.end_date IS NULL OR rfr.end_date > :currentDate)
                    AND lang.code = :languageCode
                GROUP BY rfr.id
                '
            );
        $stmt->bindValue('languageCode', LanguageTypeCode::ENGLISH);
        $stmt->bindValue('currentDate', $this->getRfrCurrentDate());
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findBySearchQuery(string $searchString, string $vehicleClass, string $audience, int $start, int $end): array
    {
        $booleanSearch = str_replace(
            ['+', '<', '>', '&', '-', '@', '(', ')', '~', '*', '"'],
            ' ',
            $searchString
        );

        $likeSearchParam = "$searchString%";

        $sqlPattern = '
            SELECT
                rfr.id AS rfrId,
                rfr.test_item_category_id AS testItemSelectorId,
                rfl.test_item_selector_name AS testItemSelectorName,
                rfr.inspection_manual_reference AS inspectionManualReference,
                rfr.manual AS manual,
                rfr.is_advisory AS isAdvisory,
                rfr.is_prs_fail AS isPrsFail,
                rfr.audience AS audience,
                rfr.start_date AS startDate,
                rfr.end_date AS endDate,
                ticlm.name AS categoryName,
                ticlm.description AS categoryDescription,
                rfl.name AS description,
                rfl.advisory_text AS advisoryText,
                rfl.inspection_manual_description AS inspectionManualDescription,
                rdc.id AS deficiencyCategoryId,
                rdc.code AS deficiencyCategoryCode,
                rdc.description AS deficiencyCategoryDescription,
                GROUP_CONCAT(vclass.id) AS vehicleClasses,
                MATCH (rfl.name, rfl.test_item_selector_name) AGAINST (:searchString IN BOOLEAN MODE) AS rank
            %s
            ORDER BY rank DESC, rfr.inspection_manual_reference ASC
            LIMIT :limitStart, :limitEnd
        ';

        $sql = sprintf($sqlPattern, $this->getCommonSqlForSearchingRfrs());


        $stmt = $this->em->getConnection()->prepare($sql);

        $stmt->bindValue('languageCode', LanguageTypeCode::ENGLISH);
        $stmt->bindValue('searchString', $booleanSearch);
        $stmt->bindValue('vehicleClass', $vehicleClass);
        $stmt->bindValue('audience', $audience);
        $stmt->bindValue('likeSearchParam', $likeSearchParam);
        $stmt->bindValue('limitStart', (int) $start, \PDO::PARAM_INT);
        $stmt->bindValue('limitEnd', (int) $end, \PDO::PARAM_INT);
        $stmt->bindValue('currentDate', $this->getRfrCurrentDate());
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function count(string $searchString, string $vehicleClass, string $audience): int
    {
        $booleanSearch = str_replace(
            ['+', '<', '>', '&', '-', '@', '(', ')', '~', '*', '"'],
            ' ',
            $searchString
        );

        $likeSearchParam = "$searchString%";

        $sqlPattern = '
                SELECT COUNT(id) AS amount
                FROM (
                    SELECT rfr.id
                    %s
                ) rfr_id
                ';

        $sql = sprintf($sqlPattern, $this->getCommonSqlForSearchingRfrs());

        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->bindValue('languageCode', LanguageTypeCode::ENGLISH);
        $stmt->bindValue('searchString', $booleanSearch);
        $stmt->bindValue('vehicleClass', $vehicleClass);
        $stmt->bindValue('audience', $audience);
        $stmt->bindValue('likeSearchParam', $likeSearchParam);
        $stmt->bindValue('currentDate', $this->getRfrCurrentDate());

        $stmt->execute();

        return (int) $stmt->fetch()["amount"];
    }

    private function getCommonSqlForSearchingRfrs(): string
    {
        return "
            FROM reason_for_rejection rfr
            JOIN rfr_vehicle_class_map vc ON rfr.id = vc.rfr_id
            JOIN vehicle_class vclass ON vc.vehicle_class_id = vclass.id
            JOIN rfr_language_content_map rfl ON rfl.rfr_id = rfr.id
            JOIN language_type lang ON rfl.language_type_id = lang.id
            JOIN test_item_category tis ON rfr.test_item_category_id = tis.id
            LEFT JOIN ti_category_language_content_map ticlm
                ON ticlm.test_item_category_id = tis.id
                AND ticlm.language_lookup_id = (SELECT id FROM language_type WHERE code = :languageCode)
            JOIN rfr_deficiency_category rdc ON rdc.id = rfr.rfr_deficiency_category_id

            WHERE vclass.code = :vehicleClass
                AND (rfr.audience = :audience OR rfr.audience = 'b')
                AND (
                    MATCH (rfl.name, rfl.test_item_selector_name) AGAINST (:searchString IN BOOLEAN MODE)
                    OR rfr.inspection_manual_reference LIKE :likeSearchParam
                    OR rfr.id LIKE :likeSearchParam
                )
                AND rfr.spec_proc = 0
                AND (rfr.end_date IS NULL OR rfr.end_date > :currentDate)
                AND rfr.start_date <= :currentDate
                AND lang.code = :languageCode
            GROUP BY rfr.id
            ";
    }

    private function getRfrCurrentDate(): string
    {
        return $this->rfrCurrentDateFaker->getCurrentDateTime()->format(DateTimeApiFormat::FORMAT_ISO_8601_DATE_ONLY);
    }
}
