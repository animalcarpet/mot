<?php

namespace DvsaMotApiTest\Model\OutputFormat;

use DvsaMotApi\Model\OutputFormat\OutputFormatDataTablesVehicle;
use DvsaCommon\Date\DateUtils;

/**
 * Class OutputFormatDataTablesVehicleTest.
 */
class OutputFormatDataTablesVehicleTest extends \PHPUnit_Framework_TestCase
{
    /* @var \DvsaMotApi\Model\OutputFormat\OutputFormatDataTablesVehicle */
    protected $outputFormat;
    /* @var \DateTime */
    protected $date;

    public function setUp()
    {
        $this->outputFormat = new OutputFormatDataTablesVehicle();
        $this->date = new \DateTime();
    }

    public function testOutputFormatDataTablesVehicleExtractItem()
    {
        $result = [];
        $this->outputFormat->extractItem($result, 1, $this->getVehicleEs());
        $this->assertSame($this->getVehicleJsonDataTable(), $result);
    }

    protected function getVehicleEs()
    {
        return [
            '_source' => [
                'id' => '1',
                'vin' => '1M8GDM9AXKP042788',
                'registration' => 'FNZ6110',
                'make' => 'Renault',
                'model' => 'Clio',
                'displayDate' => DateUtils::toIsoString($this->date),
                'updatedDate_display' => $this->date->format('d M Y'),
                'updatedDate_timestamp' => strtotime($this->date->format('d M Y h:i')),
            ],
        ];
    }

    protected function getVehicleJsonDataTable()
    {
        return [
            '1' => [
                'vin' => '1M8GDM9AXKP042788',
                'registration' => 'FNZ6110',
                'make' => 'Renault',
                'model' => 'Clio',
                'displayDate' => $this->date->format('d M Y'),
            ],
        ];
    }
}
