<?php

namespace Dashboard\ViewModel;

use PHPUnit_Framework_TestCase;

class createReplacementDuplicateCertificateViewModelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider rDCLinkDataProvider
     *
     * @param bool $hasTestInProgress
     * @param bool $canViewReplacementDuplicateLink
     * @param bool $expectedResult
     */
    public function testIfReplacementDuplicateCertificateLinkIsVisible(
        $hasTestInProgress,
        $canViewReplacementDuplicateLink,
        $expectedResult
    )
    {
        $rDCViewModel = $this->createReplacementDuplicateCertificateViewModel(
            $hasTestInProgress,
            $canViewReplacementDuplicateLink
        );

        $this->assertEquals($expectedResult, $rDCViewModel->canViewReplacementDuplicateCertificateLink());
    }

    /**
     * @dataProvider hasTestInProgressDataProvider
     *
     * @param bool $hasTestInProgress
     * @param bool $expectedResult
     */
    public function testIfTestIsInProgress($hasTestInProgress, $expectedResult)
    {
        $rDCViewModel = $this->createReplacementDuplicateCertificateViewModel($hasTestInProgress, true);

        $this->assertEquals($expectedResult, $rDCViewModel->hasTestInProgress());
    }

    /**
     * @return array
     */
    public function rDCLinkDataProvider()
    {
        return [
            [false, true, true],
            [true, true, false],
            [true, false, false]
        ];
    }

    /**
     * @return array
     */
    public function hasTestInProgressDataProvider()
    {
        return [
            [true, true],
            [false, false],
        ];
    }

    /**
     * @param $hasTestInProgress
     * @param $canViewReplacementDuplicateLink
     *
     * @return ReplacementDuplicateCertificateViewModel
     */
    private function createReplacementDuplicateCertificateViewModel(
        $hasTestInProgress,
        $canViewReplacementDuplicateLink
    )
    {
        return new ReplacementDuplicateCertificateViewModel(
            $hasTestInProgress,
            $canViewReplacementDuplicateLink
        );
    }
}

