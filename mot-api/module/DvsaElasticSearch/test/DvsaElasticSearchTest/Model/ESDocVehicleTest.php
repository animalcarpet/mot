<?php

namespace DvsaElasticSearchTest\Model;

use DvsaElasticSearch\Model\ESDocVehicle;
use DvsaEntities\Entity\ModelDetail;
use DvsaEntities\Entity\Vehicle;
use DvsaEntities\Entity\Make;
use DvsaEntities\Entity\Model;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommon\Date\DateUtils;

/**
 * Class ESDocVehicleTest.
 */
class ESDocVehicleTest extends \PHPUnit_Framework_TestCase
{
    /* @var \DvsaElasticSearch\Model\ESDocVehicle */
    protected $docVehicleTest;
    /* @var \DateTime */
    protected $date;

    public function setUp()
    {
        $this->docVehicleTest = new ESDocVehicle();
        $this->date = new \DateTime();
    }

    public function testEsDocVehicleAsEsDataReturnValue()
    {
        $vehicleData = $this->getVehicleData();
        $vehicle = $this->getVehicleEntity();

        $this->assertSame($vehicleData, $this->docVehicleTest->asEsData($vehicle));
    }

    public function testEsDocVehicleAsJsonReturnValueDataTable()
    {
        $vehicleFromEs = $this->getVehicleEs();
        $vehicleFromEs['format'] = 'DATA_TABLES';
        $this->assertSame($this->getVehicleJsonDataTable(), $this->docVehicleTest->asJson($vehicleFromEs));
    }

    public function testEsDocVehicleAsJsonReturnException()
    {
        $vehicleFromEs = $this->getVehicleEs();
        $vehicleFromEs['format'] = 'INVALID_FORMAT';
        $this->setExpectedException(BadRequestException::class);
        $this->docVehicleTest->asJson($vehicleFromEs);
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

    protected function getVehicleEs()
    {
        return [
            'hits' => [
                'hits' => [
                    [
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
                    ],
                ],
            ],
        ];
    }

    protected function getVehicleData()
    {
        return [
            'id' => 1,
            'vin' => 'hdh7htref0gr5greh',
            'registration' => 'FNZ 6JZ',
            'make' => 'Porshe',
            'model' => '911 Turbo',
            'displayDate' => DateUtils::toIsoString($this->date),
            'updatedDate_display' => $this->date->format('d M Y'),
            'updatedDate_timestamp' => strtotime($this->date->format('d M Y h:i')),
        ];
    }

    protected function getVehicleEntity()
    {
        $make = new Make();
        $make->setName('Porshe');

        $model = new Model();
        $model->setName('911 Turbo');
        $model->setMake($make);

        $modelDetail = new ModelDetail();
        $modelDetail->setModel($model);

        $vehicle = new Vehicle();
        $vehicle
            ->setId(1)
            ->setRegistration('FNZ 6JZ')
            ->setModelDetail($modelDetail)
            ->setVin('hdh7htref0gr5greh')
            ->setRegistration('FNZ 6JZ')
            ->setLastUpdatedOn($this->date);

        return $vehicle;
    }
}
