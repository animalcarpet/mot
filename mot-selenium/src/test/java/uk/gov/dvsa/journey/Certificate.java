package uk.gov.dvsa.journey;

import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.api.response.Vehicle;
import uk.gov.dvsa.domain.navigation.PageNavigator;
import uk.gov.dvsa.ui.pages.VehicleSearchPage;
import uk.gov.dvsa.ui.pages.mot.MotTestHistoryPage;
import uk.gov.dvsa.ui.pages.mot.MotTestSearchPage;
import uk.gov.dvsa.ui.pages.mot.EnforcementTestSummaryPage;
import uk.gov.dvsa.ui.pages.mot.certificates.*;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.equalToIgnoringCase;


public class Certificate {

    private PageNavigator pageNavigator;
    DuplicateReplacementCertificateTestHistoryPage certificateTestHistoryPage;
    MotTestCertificatePage certificatePage;
    ReplacementCertificateUpdatePage updatePage;
    ReplacementCertificateReviewPage reviewPage;
    CertificateEditUpdatePage editSuccessfulPage;
    private boolean isPrintButtonDisplayed;
    private boolean declarationSuccessful = false;
    private boolean declarationFor2FaSuccessful = false;

    private static final String DECLARATION_STATEMENT = "I confirm that this MOT transaction has been conducted in accordance with " +
            "the conditions of authorisation which includes compliance with the MOT testing guide, the requirements for " +
            "authorisation, the appropriate MOT Inspection Manual and any other instructions issued by DVSA.";

    private static final String DECLARATION_STATEMENT_2FA = "By saving this replacement certificate you confirm that the changes you " +
            "have made are in line with DVSA conditions for MOT testing.";

    public Certificate(PageNavigator pageNavigator) { this.pageNavigator = pageNavigator; }

    public boolean isDeclarationStatementDisplayed() {
        return declarationSuccessful;
    }

    public boolean isDeclarationStatementFor2FaDisplayed() {
        return declarationFor2FaSuccessful;
    }

    public void createReplacementCertificate(User tester, Vehicle vehicle, String motTestId) throws IOException, URISyntaxException {
        DuplicateReplacementCertificateTestHistoryPage duplicateReplacementCertificateTestHistoryPage = pageNavigator.gotoDuplicateReplacementCertificateTestHistoryPage(tester, vehicle);
        ReplacementCertificateUpdatePage replacementCertificateUpdatePage = duplicateReplacementCertificateTestHistoryPage.clickEditButton(motTestId);
        replacementCertificateUpdatePage.submitNoOdometerOption();

        ReplacementCertificateReviewPage replacementCertificateReviewPage =
                replacementCertificateUpdatePage.reviewChangesButton(ReplacementCertificateReviewPage.class);

        if (replacementCertificateReviewPage.isPinBoxDisplayed()) {
            assertThat(replacementCertificateReviewPage.getDeclarationText(), equalToIgnoringCase(DECLARATION_STATEMENT));
            declarationSuccessful = true;
        } else {
            assertThat(replacementCertificateReviewPage.getDeclarationText(), equalToIgnoringCase(DECLARATION_STATEMENT_2FA));
            declarationFor2FaSuccessful = true;
        }
    }

    public void printReplacementPage(User user, Vehicle vehicle, String motTestNumber) throws IOException, URISyntaxException {
        certificateTestHistoryPage = pageNavigator.navigateToPage(user, VehicleSearchPage.REPLACEMENT_PATH, VehicleSearchPage.class)
                .searchVehicle(vehicle).selectVehicle(DuplicateReplacementCertificateTestHistoryPage.class);;

        certificatePage = certificateTestHistoryPage.viewTest(motTestNumber, MotTestCertificatePage.class);

        isPrintButtonDisplayed = certificatePage.isReprintButtonDisplayed();
    }

    private void updateCertificatePage(User user, Vehicle vehicle, String motTestNumber) throws IOException, URISyntaxException {
        certificateTestHistoryPage = pageNavigator.navigateToPage(user, VehicleSearchPage.REPLACEMENT_PATH, VehicleSearchPage.class)
                .searchVehicle(vehicle).selectVehicle(DuplicateReplacementCertificateTestHistoryPage.class);

        updatePage = certificateTestHistoryPage.editTest(motTestNumber, ReplacementCertificateUpdatePage.class);
    }

    public boolean isReprintButtonDisplayed() {
        return isPrintButtonDisplayed;
    }

    public void updateOdometer() {
        updatePage.submitNoOdometerOption();
        updatePage.reviewChangesButton(ReplacementCertificateReviewPage.class);
    }

    public Certificate updateCertificate(User user, Vehicle vehicle, String motTestNumber) throws IOException, URISyntaxException {
        updateCertificatePage(user, vehicle, motTestNumber);
        return this;
    }

    public Certificate setOdometerToNull(){
        updateOdometer();
        reviewPage = new ReplacementCertificateReviewPage(pageNavigator.getDriver());
        editSuccessfulPage = reviewPage.confirmAndPrint(CertificateEditUpdatePage.class);

        isPrintButtonDisplayed = editSuccessfulPage.isPrintButtonDisplayed();

        return this;
    }

    public boolean isEditButtonDisplayed() {
        return updatePage.isEditOdometerButtonDisplayed();
    }

    public void viewSummaryAsVehicleExaminer(User user, String siteNumber, String testNumber)
            throws IOException, URISyntaxException {
        MotTestSearchPage testSearchPage = pageNavigator
                .navigateToPage(user, MotTestSearchPage.PATH, MotTestSearchPage.class);

        MotTestHistoryPage testHistoryPage = testSearchPage.fillSearchValue(siteNumber)
                .clickSearchButton(MotTestHistoryPage.class);

        EnforcementTestSummaryPage summaryPage =
                testHistoryPage.selectMotTestFromTableById(testNumber, EnforcementTestSummaryPage.class);
        isPrintButtonDisplayed = summaryPage.printCertificateButtonExists(testNumber);
    }

    public void updateAndReviewCeritficate(User user, Vehicle vehicle, String testNumber) throws IOException, URISyntaxException {
        updateCertificatePage(user, vehicle, testNumber);
        updateOdometer();
    }

    public boolean isPinBoxDisplayed() {
        ReplacementCertificateReviewPage replacementReviewPage =
                new ReplacementCertificateReviewPage(pageNavigator.getDriver());
        return replacementReviewPage.isPinBoxDisplayed();
    }
}
