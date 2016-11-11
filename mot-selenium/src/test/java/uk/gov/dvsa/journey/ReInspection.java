package uk.gov.dvsa.journey;

import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.navigation.PageNavigator;
import uk.gov.dvsa.helper.RandomDataGenerator;
import uk.gov.dvsa.helper.enums.Comparison;
import uk.gov.dvsa.ui.pages.AssessmentDetailsConfirmationPage;
import uk.gov.dvsa.ui.pages.mot.*;

import java.io.IOException;

public class ReInspection {

    private PageNavigator pageNavigator;
    private static final String STATUTORY_APPEAL = "Statutory Appeal";
    private static final String INVERTED_APPEAL = "Inverted Appeal";
    private static final String TARGETED_REINSPECTION = "Targeted Reinspection";

    public ReInspection(PageNavigator pageNavigator) {
        this.pageNavigator = pageNavigator;
    }

    public TestResultsEntryGroupAPageInterface startReInspection(User user, String testId, String appealType) throws IOException {
        String path = String.format(EnforcementTestSummaryPage.PATH, testId);

        return pageNavigator.navigateToPage(user, path, EnforcementTestSummaryPage.class)
                            .selectInspectionType(appealType)
                            .enterComplaintReference(RandomDataGenerator.generateRandomString())
                            .startReInspection();
    }

    public TestSummaryPage statutoryAppealTestSummaryPage(User user, String testId) throws IOException {
        return showTestSummaryPageFor(user, testId, STATUTORY_APPEAL);
    }

    public TestSummaryPage invertedAppealTestSummaryPage(User user, String testId) throws IOException {
        return showTestSummaryPageFor(user, testId, INVERTED_APPEAL);
    }

    private TestSummaryPage showTestSummaryPageFor(User user, String testId, String appealType) throws IOException {
        TestResultsEntryGroupAPageInterface reInspectionPage = startReInspection(user, testId, appealType);
        reInspectionPage.completeTestDetailsWithPassValues(false);

        return reInspectionPage.clickReviewTestButton();
    }

    public String statutoryAppeal(User user, String motTestId, String siteNumber, Comparison comparison)
            throws IOException {
        return conductAppeal(STATUTORY_APPEAL, user, motTestId, siteNumber, comparison);
    }

    public String invertedAppeal(User user, String motTestId, String siteNumber, Comparison comparison)
            throws IOException {
        return conductAppeal(INVERTED_APPEAL, user, motTestId, siteNumber, comparison);
    }

    public String getReInspectionAssessmentMessage(User user, String motTestId) throws IOException {
        TestSummaryPage summaryPage = showTestSummaryPageFor(user, motTestId, TARGETED_REINSPECTION);
        AssessmentDetailsConfirmationPage confirmationPage = summaryPage.clickFinishButton(TestCompletePage.class)
                .clickCompareResultsButton()
                .completeJustificationWithRandomValues()
                .recordAssesment();

        return confirmationPage.getValidationMessageText();
    }

    private String conductAppeal(String appealType, User user, String motTestId, String siteNumber, Comparison comparison)
            throws IOException {
        TestCompletePage testCompletePage = showTestSummaryPageFor(user, motTestId, appealType)
                .fillSiteIdInput(siteNumber)
                .clickFinishButton(TestCompletePage.class);

        DifferenceBetweenTestPage betweenTestPage = testCompletePage.clickCompareResultsButton();
        betweenTestPage.setScoreByFailureName(comparison);

        return betweenTestPage.indicativeCaseOutcome();
    }
}
