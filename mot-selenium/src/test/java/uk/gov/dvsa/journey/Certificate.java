package uk.gov.dvsa.journey;

import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.api.response.Vehicle;
import uk.gov.dvsa.domain.navigation.PageNavigator;
import uk.gov.dvsa.ui.pages.mot.MotTestHistoryPage;
import uk.gov.dvsa.ui.pages.mot.MotTestSearchPage;
import uk.gov.dvsa.ui.pages.mot.EnforcementTestSummaryPage;
import uk.gov.dvsa.ui.pages.mot.certificates.*;
import uk.gov.dvsa.ui.pages.mot.certificates.ReplacementCertificateResultsPage;
import uk.gov.dvsa.ui.pages.mot.certificates.ReplacementCertificateViewPage;
import uk.gov.dvsa.ui.pages.mot.certificates.VehicleSearchByVinPage;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.equalToIgnoringCase;


public class Certificate {

    private PageNavigator pageNavigator;
    ReplacementCertificateViewPage certificatePage;
    ReplacementCertificateUpdatePage updatePage;
    ReplacementCertificateReviewPage reviewPage;
    ReplacementCertificateUpdateSuccessfulPage updateSuccessfulPage;
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
        ReplacementCertificateResultsPage replacementCertificateResultsPage = pageNavigator.gotoReplacementCertificateResultsPage(tester, vehicle);
        ReplacementCertificateUpdatePage replacementCertificateUpdatePage = replacementCertificateResultsPage.viewTest(motTestId).edit();
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
        viewCertificatePage(user, vehicle, motTestNumber);
        isPrintButtonDisplayed = certificatePage.isReprintButtonDisplayed();
    }

    private void updateCertificatePage(User user, Vehicle vehicle, String motTestNumber) throws IOException, URISyntaxException {
        viewCertificatePage(user, vehicle, motTestNumber);
        updatePage = certificatePage.edit();
    }

    public void viewCertificatePage(User user, Vehicle vehicle, String motTestNumber) throws IOException, URISyntaxException  {
        certificatePage = searchVehicle(user, vehicle).viewTest(motTestNumber);
    }

    public void viewOlderCertificatePage(User user, Vehicle vehicle, String motTestNumber) throws IOException, URISyntaxException  {
        certificatePage = searchVehicle(user, vehicle).viewOlderTest(vehicle.getDvsaRegistration(), motTestNumber);
    }

    public void viewCertificatePageUsingVinSearch(User user, Vehicle vehicle, String motTestNumber) throws IOException, URISyntaxException {
        certificatePage = searchVehicle(user, vehicle).viewTest(motTestNumber);
    }

    private ReplacementCertificateResultsPage searchVehicle(User user, Vehicle vehicle) throws IOException, URISyntaxException {
        return pageNavigator.navigateToPage(user, VehicleSearchByVinPage.PATH, VehicleSearchByVinPage.class).searchVehicle(vehicle);
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
        updateSuccessfulPage = reviewPage.confirmAndPrint(ReplacementCertificateUpdateSuccessfulPage.class);

        isPrintButtonDisplayed = updateSuccessfulPage.isPrintButtonDisplayed();

        return this;
    }

    public boolean isEditOdometerButtonDisplayed() {
        return updatePage.isEditOdometerButtonDisplayed();
    }

    public boolean isEditButtonDisplayed() {
        return certificatePage.isEditButtonDisplayed();
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

    public void updateAndReviewCertificate(User user, Vehicle vehicle, String testNumber) throws IOException, URISyntaxException {
        updateCertificatePage(user, vehicle, testNumber);
        updateOdometer();
    }

    public boolean isPinBoxDisplayed() {
        ReplacementCertificateReviewPage replacementReviewPage =
                new ReplacementCertificateReviewPage(pageNavigator.getDriver());
        return replacementReviewPage.isPinBoxDisplayed();
    }
}
