<?php

namespace DvsaMotTestTest\ViewModel;

use Dvsa\Mot\Frontend\MotTestModule\ViewModel\ObservedDefectCollection;
use DvsaCommon\Dto\Common\MotTestDto;

/**
 * Class ObservedDefectCollectionTest.
 */
class ObservedDefectCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider creationDataProvider
     * @group wip
     *
     * @param array $reasonsForRejection
     * @param bool  $hasFailures
     * @param bool  $hasPrs
     * @param bool  $hasAdvisories
     */
    public function testCreation(array $reasonsForRejection, $hasFailures, $hasPrs, $hasAdvisories)
    {
        $motTestMock = $this->createMotTestDto($reasonsForRejection);

        $testCollection = ObservedDefectCollection::fromMotApiData($motTestMock);

        $loopIndex = 0;
        if ($hasFailures) {
            foreach ($reasonsForRejection['FAIL'] as $fail) {
                $this->assertEquals(
                    $fail['locationLateral'],
                    $testCollection->getFailures()[$loopIndex]->getLateralLocation()
                );

                $this->assertEquals(
                    $fail['locationLongitudinal'],
                    $testCollection->getFailures()[$loopIndex]->getLongitudinalLocation()
                );

                $this->assertEquals(
                    $fail['locationVertical'],
                    $testCollection->getFailures()[$loopIndex]->getVerticalLocation()
                );

                $this->assertEquals(
                    $fail['comment'],
                    $testCollection->getFailures()[$loopIndex]->getUserComment()
                );

                $this->assertEquals(
                    $fail['failureDangerous'],
                    $testCollection->getFailures()[$loopIndex]->isDangerous()
                );

                $this->assertEquals(
                    $fail['testItemSelectorDescription'].' '.$fail['failureText'],
                    $testCollection->getFailures()[$loopIndex]->getName()
                );

                $loopIndex += 1;
            }
        }

        $loopIndex = 0;
        if ($hasPrs) {
            foreach ($reasonsForRejection['PRS'] as $prs) {
                $this->assertEquals(
                    $prs['locationLateral'],
                    $testCollection->getPrs()[$loopIndex]->getLateralLocation()
                );

                $this->assertEquals(
                    $prs['locationLongitudinal'],
                    $testCollection->getPrs()[$loopIndex]->getLongitudinalLocation()
                );

                $this->assertEquals(
                    $prs['locationVertical'],
                    $testCollection->getPrs()[$loopIndex]->getVerticalLocation()
                );

                $this->assertEquals(
                    $prs['comment'],
                    $testCollection->getPrs()[$loopIndex]->getUserComment()
                );

                $this->assertEquals(
                    $prs['failureDangerous'],
                    $testCollection->getPrs()[$loopIndex]->isDangerous()
                );

                $this->assertEquals(
                    $prs['testItemSelectorDescription'].' '.$prs['failureText'],
                    $testCollection->getPrs()[$loopIndex]->getName()
                );

                $loopIndex += 1;
            }
        }

        $loopIndex = 0;
        if ($hasAdvisories) {
            foreach ($reasonsForRejection['ADVISORY'] as $advisory) {
                $this->assertEquals(
                    $advisory['locationLateral'],
                    $testCollection->getAdvisories()[$loopIndex]->getLateralLocation()
                );

                $this->assertEquals(
                    $advisory['locationLongitudinal'],
                    $testCollection->getAdvisories()[$loopIndex]->getLongitudinalLocation()
                );

                $this->assertEquals(
                    $advisory['locationVertical'],
                    $testCollection->getAdvisories()[$loopIndex]->getVerticalLocation()
                );

                $this->assertEquals(
                    $advisory['comment'],
                    $testCollection->getAdvisories()[$loopIndex]->getUserComment()
                );

                $this->assertEquals(
                    $advisory['failureDangerous'],
                    $testCollection->getAdvisories()[$loopIndex]->isDangerous()
                );

                $this->assertEquals(
                    $advisory['testItemSelectorDescription'].' '.$advisory['failureText'],
                    $testCollection->getAdvisories()[$loopIndex]->getName()
                );
                $loopIndex += 1;
            }
        }
    }

    /**
     * @return array
     */
    public function creationDataProvider()
    {
        return [
            'All keys present' => [$this->getTestData(true, true, true), true, true, true],
            'Missing ADVISORY key' => [$this->getTestData(true, true, false), true, true, false],
            'Missing PRS Key' => [$this->getTestData(true, false, true), true, false, true],
            'Missing FAIL key' => [$this->getTestData(false, true, true), false, true, true],
            'Missing all keys' => [$this->getTestData(false, false, false), false, false, false],
        ];
    }

    /**
     * @param $reasonsForRejection
     *
     * @return MotTestDto|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createMotTestDto($reasonsForRejection)
    {
        $motTestMock = $this->getMockBuilder(MotTestDto::class)->getMock();
        $motTestMock->expects($this->atLeast(3))
            ->method('getReasonsForRejection')
            ->willReturn($reasonsForRejection);

        return $motTestMock;
    }

    /**
     * @param bool $withFailures
     * @param bool $withPrs
     * @param bool $withAdvisories
     *
     * @return array
     */
    private function getTestData($withFailures, $withPrs, $withAdvisories)
    {
        $testData = [];

        if ($withFailures) {
            $testData['FAIL'] = [
                0 => [
                    'type' => 'FAIL',
                    'locationLateral' => null,
                    'locationLongitudinal' => null,
                    'locationVertical' => null,
                    'comment' => null,
                    'failureDangerous' => false,
                    'generated' => false,
                    'customDescription' => null,
                    'onOriginalTest' => false,
                    'id' => 1,
                    'rfrId' => 8460,
                    'name' => 'Body condition',
                    'nameCy' => '',
                    'testItemSelectorDescription' => 'Body',
                    'failureText' => 'or chassis has excessive corrosion seriously affecting its strength within 30cm of the body mountings',
                    'testItemSelectorDescriptionCy' => null,
                    'failureTextCy' => 'mae\'r siasi wedi rhydu gormod, sy\'n effeithio\'n ddifrifol ar ei gryfder o fewn 30cm i fowntinau\'r corff',
                    'testItemSelectorId' => 5696,
                    'inspectionManualReference' => '6.1.B.2',
                ],
            ];
        }

        if ($withPrs) {
            $testData['PRS'] = [
                0 => [
                    'type' => 'PRS',
                    'locationLateral' => null,
                    'locationLongitudinal' => null,
                    'locationVertical' => null,
                    'comment' => null,
                    'failureDangerous' => false,
                    'generated' => false,
                    'customDescription' => null,
                    'onOriginalTest' => false,
                    'id' => 2,
                    'rfrId' => 8463,
                    'name' => 'Body condition',
                    'nameCy' => '',
                    'testItemSelectorDescription' => 'Body',
                    'failureText' => 'or chassis has an inadequate repair seriously affecting its strength within 30cm of the body mountings',
                    'testItemSelectorDescriptionCy' => null,
                    'failureTextCy' => 'mae\'r siasi wedi\'i drwsio\'n annigonol sy\'n effeithio\'n ddifrifol ar ei gryfder o fewn 30cm i fowntinau\'r corff',
                    'testItemSelectorId' => 5696,
                    'inspectionManualReference' => '6.1.B.2',
                ],
            ];
        }

        if ($withAdvisories) {
            $testData['ADVISORY'] = [
                0 => [
                    'type' => 'ADVISORY',
                    'locationLateral' => null,
                    'locationLongitudinal' => null,
                    'locationVertical' => null,
                    'comment' => null,
                    'failureDangerous' => false,
                    'generated' => false,
                    'customDescription' => null,
                    'onOriginalTest' => false,
                    'id' => 2,
                    'rfrId' => 8463,
                    'name' => 'Body condition',
                    'nameCy' => '',
                    'testItemSelectorDescription' => 'Body',
                    'failureText' => 'or chassis has an inadequate repair seriously affecting its strength within 30cm of the body mountings',
                    'testItemSelectorDescriptionCy' => null,
                    'failureTextCy' => 'mae\'r siasi wedi\'i drwsio\'n annigonol sy\'n effeithio\'n ddifrifol ar ei gryfder o fewn 30cm i fowntinau\'r corff',
                    'testItemSelectorId' => 5696,
                    'inspectionManualReference' => '6.1.B.2',
                ],
            ];
        }

        return $testData;
    }
}
