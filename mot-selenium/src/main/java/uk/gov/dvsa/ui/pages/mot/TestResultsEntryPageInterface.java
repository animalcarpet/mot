package uk.gov.dvsa.ui.pages.mot;

public interface TestResultsEntryPageInterface {

    public Boolean isClickReviewTestButtonPresent();

    public TestResultsEntryPageInterface completeTestDetailsWithPassValues();
    public TestResultsEntryPageInterface addOdometerReading(int odometerReading);
    public boolean isPassNoticeDisplayed();
    public TestSummaryPage clickReviewTestButton();

}