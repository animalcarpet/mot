<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\DvlaMakeModelMap;
use DvsaEntities\Entity\Make;
use DvsaEntities\Entity\Model;
use DvsaEntities\Entity\ModelDetail;

/**
 * Class DvlaMakeModelMapTest.
 */
class DvlaMakeModelMapTest extends \PHPUnit_Framework_TestCase
{
    public function testSettersAndGetters()
    {
        $data = [
            'id' => 1,
            'dvlaMakeCode' => 'AA',
            'dvlaModelCode' => '000',
            'make' => new Make(),
            'model' => new Model(),
            'modelDetail' => new ModelDetail(),
        ];

        $dvlaMakeModelMap = new DvlaMakeModelMap();
        $dvlaMakeModelMap
            ->setId($data['id'])
            ->setDvlaMakeCode($data['dvlaMakeCode'])
            ->setDvlaModelCode($data['dvlaModelCode'])
            ->setMake($data['make'])
            ->setModel($data['model'])
            ->setModelDetail($data['modelDetail']);

        $this->assertEquals($data['id'], $dvlaMakeModelMap->getId());
        $this->assertEquals($data['dvlaMakeCode'], $dvlaMakeModelMap->getDvlaMakeCode());
        $this->assertEquals($data['dvlaModelCode'], $dvlaMakeModelMap->getDvlaModelCode());
        $this->assertEquals($data['make'], $dvlaMakeModelMap->getMake());
        $this->assertEquals($data['model'], $dvlaMakeModelMap->getModel());
        $this->assertEquals($data['modelDetail'], $dvlaMakeModelMap->getModelDetail());
    }
}
