package uk.gov.dvsa.module;

import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.navigation.PageNavigator;
import uk.gov.dvsa.helper.RandomDataGenerator;
import uk.gov.dvsa.helper.enums.Comparison;
import uk.gov.dvsa.ui.pages.AssessmentDetailsConfirmationPage;
import uk.gov.dvsa.ui.pages.mot.*;

import java.io.IOException;

public class ReInspection {
    private PageNavigator pageNavigator;

    public ReInspection(PageNavigator pageNavigator) {
        this.pageNavigator = pageNavigator;
    }

    public String statutoryAppeal(User user, String motTestId, String siteNumber, Comparison comparison)
            throws IOException {
        return conductAppeal("Statutory Appeal", user, motTestId, siteNumber, comparison);
    }

    public String invertedAppeal(User user, String motTestId, String siteNumber, Comparison comparison)
            throws IOException {
        return conductAppeal("Inverted Appeal", user, motTestId, siteNumber, comparison);
    }

    private String conductAppeal(String appealType, User user, String motTestId, String siteNumber, Comparison comparison)
            throws IOException {
        TestCompletePage testCompletePage = showTestSummaryPageFor(user, motTestId, appealType)
                .enterSiteId(siteNumber)
                .clickFinishButton();

        DifferenceBetweenTestPage betweenTestPage = testCompletePage.clickCompareResultsButton();
        betweenTestPage.setScoreByFailureName(comparison);

        return betweenTestPage.indicativeCaseOutcome();
    }

    public TestSummaryPage statutoryAppealTestSummaryPage(User user, String testId) throws IOException {
        return showTestSummaryPageFor(user, testId, "Statutory Appeal");
    }

    public TestSummaryPage invertedAppealTestSummaryPage(User user, String testId) throws IOException {
       return showTestSummaryPageFor(user, testId, "Inverted Appeal");
    }

    private TestSummaryPage showTestSummaryPageFor(User user, String testId, String appealType) throws IOException {
        String path = String.format(EnforcementTestSummaryPage.PATH, testId);
        EnforcementTestSummaryPage enforcementTestSummaryPage =
                pageNavigator.navigateToPage(user, path, EnforcementTestSummaryPage.class);

        enforcementTestSummaryPage.selectInspectionType(appealType);
        enforcementTestSummaryPage.enterComplaintReference(RandomDataGenerator.generateRandomString());

        TestResultsEntryGroupAPageInterface reInspectionPage = enforcementTestSummaryPage.startReInspection();
        reInspectionPage.completeTestDetailsWithPassValues();

        return reInspectionPage.clickReviewTestButton();
    }

    public String targetedReInspection(User user, String motTestId) throws IOException {
        TestSummaryPage summaryPage = showTestSummaryPageFor(user, motTestId, "Targeted Reinspection");
        AssessmentDetailsConfirmationPage confirmationPage = summaryPage.clickFinishButton()
                .clickCompareResultsButton()
                .completeJustificationWithRandomValues()
                .recordAssesment();

        return confirmationPage.getValidationMessageText();
    }
}
