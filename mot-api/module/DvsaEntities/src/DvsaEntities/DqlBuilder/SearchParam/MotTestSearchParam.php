<?php
namespace DvsaEntities\DqlBuilder\SearchParam;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Dto\AbstractDataTransferObject;
use DvsaCommon\Dto\Search\MotTestSearchParamsDto;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Model\SearchParam;
use DvsaEntities\Entity\MotTest;

/**
 * Class MotTestSearchParam
 *
 * @package DvsaEntities\DqlBuilder\SearchParam
 */
class MotTestSearchParam extends SearchParam
{
    static public $esSortByColumns = [
        "0" => "testDate",
        "2" => "status",
        "3" => "vin",
        "4" => "registration",
        "5" => "startedDate",
        "6" => "make",
        "7" => "model",
        "8" => "testType",
        "9" => "siteNumber",
        "10" => "testerUsername",
        "11" => "testNumber",
    ];

    static public $dbSortByColumns = [
        "0" => ["test.startedDate", "test.completedDate"], // mot_test
        "2" => "test.status", // mot_test
        "3" => "vehicle.vin", // vehicle
        "4" => "vehicle.registration", // vehicle
        "5" => "test.completedDate, test.startedDate", // mot_test
        "6" => "make.name", // make
        "7" => "model.name", // model
        "8" => "testType.description", // mot_test_type
        "9" => "site.siteNumber", // site
        "10" => "tester.username", // person
        "11" => "test.number", // test number
    ];

    protected $siteId = null;
    protected $siteNumber = null;
    protected $testerId = null;
    protected $vehicleId = null;
    protected $registration = null;
    protected $vin = null;
    protected $dateFrom = null;
    protected $dateTo = null;
    /** @var int */
    protected $organisationId = null;
    /** @var string[] */
    protected $status = null;
    /** @var string[] */
    protected $testType = null;
    /** @var string[] */
    protected $testNumber = null;

    protected $searchRecent = false;
    protected $searchFilter = null;

    protected $sortColumnName = null;

    /** @var EntityManager */
    protected $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Performs processing of the passed search string into
     * meaningful parts.
     */
    public function process()
    {
        $this->validateInputs();
        $this->validateSearch();

        return $this;
    }

    /**
     * Apply the rules to the params
     *
     * @throws \UnexpectedValueException
     */
    protected function validateInputs()
    {
        $hasOrganisationId = $this->getOrganisationId() > 0;
        $hasSiteId         = (int)$this->getSiteId() > 0;
        $hasSiteNumber     = strlen($this->getSiteNumber()) > 0;
        $hasTesterId       = (int)$this->getTesterId() > 0;
        $hasVehicleId      = (int)$this->getVehicleId() > 0;
        $hasVrm            = strlen($this->getRegistration()) > 0;
        $hasVin            = strlen($this->getVin()) > 0;
        $hasTestNumber     = strlen($this->getTestNumber()) > 0;

        if (!($hasOrganisationId | $hasTesterId | $hasSiteId | $hasSiteNumber | $hasVrm | $hasVin | $hasVehicleId | $hasTestNumber)) {
            throw new \UnexpectedValueException(
                'Invalid search. One of site number, tester, vehicle, vrm or vin id must be passed.'
            );
        }
        if ($this->checkIfMultipleInputs([$hasOrganisationId, $hasSiteId, $hasSiteNumber, $hasTesterId, $hasVehicleId, $hasVrm, $hasVin, $hasTestNumber]) > 1) {
            throw new \UnexpectedValueException(
                'Invalid search. Only one of site number, tester, vehicle, vrm or vin id must be passed.'
            );
        }
    }

    protected function validateSearch()
    {
        if (strlen($this->getSiteId()) > 0 && strlen($this->sanitizeWords($this->getSiteId())) == 0) {
            throw new \UnexpectedValueException('No results found for that site');
        }
        if (strlen($this->getSiteNumber()) > 0 && strlen($this->sanitizeWords($this->getSiteNumber())) == 0) {
            throw new \UnexpectedValueException('No results found for that site');
        }
        if (strlen($this->getTesterId()) > 0 && strlen($this->sanitizeWords($this->getTesterId())) == 0) {
            throw new \UnexpectedValueException('No results found for that tester');
        }
        if (strlen($this->getVehicleId()) > 0 && strlen($this->sanitizeWords($this->getVehicleId())) == 0) {
            throw new \UnexpectedValueException('No results found for that vehicle');
        }
        if (strlen($this->getRegistration()) > 0 && strlen($this->sanitizeWords($this->getRegistration())) == 0) {
            throw new \UnexpectedValueException('No results found for that vehicle');
        }
        if (strlen($this->getVin()) > 0 && strlen($this->sanitizeWords($this->getVin())) == 0) {
            throw new \UnexpectedValueException('No results found for that vehicle');
        }
        if (strlen($this->getTestNumber()) > 0 && strlen($this->sanitizeWords($this->getTestNumber())) == 0) {
            throw new \UnexpectedValueException('No results found for that test number');
        }
    }

    /**
     * This function is checking that only one search input is passed
     *
     * @param array $params
     * @return int
     */
    protected function checkIfMultipleInputs($params)
    {
        $count = 0;
        foreach ($params as $param) {
            if ($param) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            "format"          => $this->getFormat(),
            "siteId"          => $this->getSiteId(),
            "siteNumber"      => $this->getSiteNumber(),
            "testNumber"      => $this->getTestNumber(),
            "testerId"        => $this->getTesterId(),
            "searchRecent"    => $this->getSearchRecent(),
            "registration"    => $this->getRegistration(),
            "vehicleId"       => $this->getVehicleId(),
            "vin"             => $this->getVin(),
            "searchFilter"    => $this->getSearchFilter(),
            "dateFrom"        => $this->getDateFrom(),
            "dateTo"          => $this->getDateTo(),
            "sortColumnId"    => $this->getSortColumnId(),
            "sortColumnName"  => $this->getSortColumnName(),
            "sortDirection"   => $this->getSortDirection(),
            "rowCount"        => $this->getRowCount(),
            "start"           => $this->getStart(),
        ];
    }

    /**
     * @param string $sortColumnName
     *
     * @return $this
     */
    public function setSortColumnName($sortColumnName)
    {
        $this->sortColumnName = $sortColumnName;

        return $this;
    }

    /**
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getSortColumnName()
    {
        if ($this->sortColumnName !== null) {
            return $this->sortColumnName;
        }

        if (isset(self::$esSortByColumns[$this->getSortColumnId()])) {
            return self::$esSortByColumns[$this->getSortColumnId()];
        }
        throw new \InvalidArgumentException('Unknown sort column: ' . $this->getSortColumnId());
    }

    /**
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getSortColumnNameDatabase()
    {
        $sortColumn = ArrayUtils::tryGet(self::$dbSortByColumns, $this->getSortColumnId(), false);
        if ($sortColumn) {
            return $sortColumn;
        }

        throw new \InvalidArgumentException('Unknown sort column: ' . $this->getSortColumnId());
    }

    /**
     * @param $siteId
     * @return $this
     */
    public function setSiteId($siteId)
    {
        $this->siteId = $siteId;
        return $this;
    }

    /**
     * @return null
     */
    public function getSiteId()
    {
        return $this->siteId;
    }

    /**
     * @param null $siteNumber
     *
     * @return $this
     */
    public function setSiteNumber($siteNumber)
    {
        $this->siteNumber = $siteNumber;
        return $this;
    }

    /**
     * @return null
     */
    public function getSiteNumber()
    {
        return $this->siteNumber;
    }

    /**
     * @param null $testerId
     *
     * @return $this
     */
    public function setTesterId($testerId)
    {
        $this->testerId = $testerId;
        return $this;
    }

    /**
     * @return null
     */
    public function getTesterId()
    {
        return $this->testerId;
    }

    /**
     * @param \DateTime $dateFrom
     *
     * @return $this
     */
    public function setDateFrom(\DateTime $dateFrom)
    {
        $this->dateFrom = $dateFrom;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateFrom()
    {
        return $this->dateFrom;
    }

    /**
     * @param \DateTime $dateTo
     *
     * @return $this
     */
    public function setDateTo(\DateTime $dateTo)
    {
        $this->dateTo = $dateTo;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateTo()
    {
        return $this->dateTo;
    }

    /**
     * @param null $searchFilter
     *
     * @return $this
     */
    public function setSearchFilter($searchFilter)
    {
        $this->searchFilter = $searchFilter;
        return $this;
    }

    /**
     * @return null
     */
    public function getSearchFilter()
    {
        return $this->searchFilter;
    }

    /**
     * @param null $vin
     *
     * @return $this
     */
    public function setVin($vin)
    {
        $this->vin = $vin;
        return $this;
    }

    /**
     * @return null
     */
    public function getVin()
    {
        return $this->vin;
    }

    /**
     * @param boolean $searchRecent
     *
     * @return $this
     */
    public function setSearchRecent($searchRecent)
    {
        $this->searchRecent = $searchRecent;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getSearchRecent()
    {
        return $this->searchRecent;
    }

    /**
     * @return null
     */
    public function getRegistration()
    {
        return $this->registration;
    }

    /**
     * @param null $registration
     *
     * @return $this
     */
    public function setRegistration($registration)
    {
        $this->registration = $registration;
        return $this;
    }

    /**
     * @return null
     */
    public function getVehicleId()
    {
        return $this->vehicleId;
    }

    /**
     * @param null $vehicleId
     *
     * @return $this
     */
    public function setVehicleId($vehicleId)
    {
        $this->vehicleId = $vehicleId;
        return $this;
    }

    /**
     * @return int
     */
    public function getOrganisationId()
    {
        return $this->organisationId;
    }

    /**
     * @param int $organisationId
     *
     * @return $this
     */
    public function setOrganisationId($organisationId)
    {
        $this->organisationId = (int)$organisationId;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string[] $status
     *
     * @return $this
     */
    public function setStatus(array $statuses)
    {
        $this->status = $statuses;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getTestType()
    {
        return $this->testType;
    }

    /**
     * @param string[] $testType
     *
     * @return $this
     */
    public function setTestType($testType)
    {
        $this->testType = $testType;

        return $this;
    }

    /**
     * @return \string[]
     */
    public function getTestNumber()
    {
        return $this->testNumber;
    }

    /**
     * @param \string[] $testNumber
     * @return MotTestSearchParam
     */
    public function setTestNumber($testNumber)
    {
        $this->testNumber = $testNumber;
        return $this;
    }

    /**
     * @param MotTestSearchParamsDto $dto
     *
     * @return $this
     */
    public function fromDto($dto)
    {
        if (!$dto instanceof MotTestSearchParamsDto) {
            throw new \InvalidArgumentException(
                __METHOD__ . ' Expects instance of MotTestSearchParamsDto, you passed ' . get_class($dto)
            );
        }

        parent::fromDto($dto);

        $this->setSiteId($dto->getSiteId());
        $this->setSiteNumber($dto->getSiteNr());
        $this->setTesterId($dto->getPersonId());
        $this->setVehicleId($dto->getVehicleId());
        $this->setRegistration($dto->getVehicleRegNr());
        $this->setVin($dto->getVehicleVin());
        $this->setOrganisationId($dto->getOrganisationId());
        $this->setStatus($dto->getStatus());
        $this->setTestType($dto->getTestType());
        $this->setTestNumber($dto->getTestNumber());

        $dateTs = (int) $dto->getDateFromTs();
        if ($dateTs) {
            $this->setDateFrom(new \DateTime('@' . $dateTs));
        }

        $dateTs = (int) $dto->getDateToTs();
        if ($dateTs) {
            $this->setDateTo(new \DateTime('@' . $dateTs));
        }

        $this->setSearchRecent($dto->isSearchRecent());
        $this->setSearchFilter($dto->getFilter());

        return $this;
    }

    /**
     * Map parameters to MotTestSearchParamsDto
     *
     * @param AbstractDataTransferObject $dto
     *
     * @return MotTestSearchParamsDto
     */
    public function toDto(AbstractDataTransferObject &$dto = null)
    {
        $dto = new MotTestSearchParamsDto();

        parent::toDto($dto);

        $dto->setSiteId($this->getSiteId());
        $dto->setSiteNr($this->getSiteNumber());
        $dto->setPersonId($this->getTesterId());
        $dto->setVehicleId($this->getVehicleId());
        $dto->setVehicleRegNr($this->getRegistration());
        $dto->setVehicleVin($this->getVin());

        if ($this->getDateFrom() instanceof \DateTime) {
            $dto->setDateFromTs($this->getDateFrom()->getTimestamp());
        }

        if ($this->getDateTo() instanceof \DateTime) {
            $dto->setDateToTs($this->getDateTo()->getTimestamp());
        }

        $dto->setOrganisationId($this->getOrganisationId());
        $dto->setStatus($this->getStatus());
        $dto->setTestType($this->getTestType());
        $dto->setTestNumber($this->getTestNumber());

        $dto->setIsSearchRecent($this->getSearchRecent());
        $dto->setFilter($this->getSearchFilter());

        return $dto;
    }

    /**
     * @return \DvsaEntities\Repository\MotTestRepository
     */
    public function getRepository()
    {
        return $this->entityManager->getRepository(MotTest::class);
    }
}
