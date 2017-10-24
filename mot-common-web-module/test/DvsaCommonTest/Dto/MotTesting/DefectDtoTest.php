<?php
/**
 * This file is part of the DVSA MOT Common project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaCommonTest\Dto\MotTesting;

use DvsaCommon\Dto\MotTesting\DefectDto;
use DvsaCommon\Enum\RfrDeficiencyCategoryCode;
use DvsaCommonTest\Dto\AbstractDtoTester;
use DvsaEntities\Entity\RfrDeficiencyCategory;
use JsonSerializable;

class DefectDtoTest extends AbstractDtoTester
{
    protected $dtoClassName = DefectDto::class;

    public function testIsJsonSerializableWithFullData()
    {
        $dto = new DefectDto();

        $dto->setId(1);
        $dto->setParentCategoryId(2);
        $dto->setDescription('Defect description');
        $dto->setAdvisoryText('This is a defect');
        $dto->setInspectionManualReference('A.B.1');
        $dto->setAdvisory(true);
        $dto->setPrs(false);
        $dto->setFailure(false);
        $dto->setDeficiencyCategoryCode(RfrDeficiencyCategoryCode::PRE_EU_DIRECTIVE);

        $this->assertInstanceOf(JsonSerializable::class, $dto);

        $serializableData = $dto->jsonSerialize();
        $this->assertEquals(1, $serializableData['id']);
        $this->assertEquals(2, $serializableData['parentCategoryId']);
        $this->assertEquals('Defect description', $serializableData['description']);
        $this->assertEquals('This is a defect', $serializableData['advisoryText']);
        $this->assertEquals('A.B.1', $serializableData['inspectionManualReference']);
        $this->assertEquals(true, $serializableData['advisory']);
        $this->assertEquals(false, $serializableData['prs']);
        $this->assertEquals(false, $serializableData['failure']);
        $this->assertEquals(RfrDeficiencyCategoryCode::PRE_EU_DIRECTIVE, $serializableData['deficiencyCategoryCode']);

        $jsonData = json_encode($dto);
        $this->assertInternalType('string', $jsonData);

        $fromJson = json_decode($jsonData, true);
        $this->assertEquals(1, $fromJson['id']);
        $this->assertEquals(2, $fromJson['parentCategoryId']);
        $this->assertEquals('Defect description', $fromJson['description']);
        $this->assertEquals('This is a defect', $fromJson['advisoryText']);
        $this->assertEquals('A.B.1', $fromJson['inspectionManualReference']);
        $this->assertEquals(true, $fromJson['advisory']);
        $this->assertEquals(false, $fromJson['prs']);
        $this->assertEquals(false, $fromJson['failure']);
        $this->assertEquals(RfrDeficiencyCategoryCode::PRE_EU_DIRECTIVE, $fromJson['deficiencyCategoryCode']);
    }
}