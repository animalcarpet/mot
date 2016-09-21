package uk.gov.dvsa.ui.pages.mot;

import uk.gov.dvsa.domain.model.mot.CancelTestReason;

public interface TestResultsEntryPageInterface {

    public Boolean isClickReviewTestButtonPresent();

    public TestResultsEntryPageInterface completeTestDetailsWithPassValues();
    public TestResultsEntryPageInterface addOdometerReading(int odometerReading);
    public boolean isPassNoticeDisplayed();
    public TestSummaryPage clickReviewTestButton();
    public TestAbandonedPage abandonMotTest(CancelTestReason reason);
    public TestAbortedPage abortMotTest(CancelTestReason reason);
    public ReasonToCancelTestPage clickCancelTest();

}