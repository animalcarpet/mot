package uk.gov.dvsa.ui.pages.mot;

import uk.gov.dvsa.ui.pages.braketest.BrakeTestResultsPage;

public interface TestResultsEntryGroupAPageInterface extends TestResultsEntryPageInterface {
    public BrakeTestResultsPage completeTestWithFloorBrakeTestsWithLockBoxes();

    public BrakeTestResultsPage completeTestWithRollerBrakeTestsWithLockBoxes();
}