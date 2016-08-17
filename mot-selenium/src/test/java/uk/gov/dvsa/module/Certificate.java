package uk.gov.dvsa.module;

import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.vehicle.Vehicle;
import uk.gov.dvsa.domain.navigation.PageNavigator;
import uk.gov.dvsa.ui.pages.VehicleSearchPage;
import uk.gov.dvsa.ui.pages.mot.MotTestHistoryPage;
import uk.gov.dvsa.ui.pages.mot.MotTestSearchPage;
import uk.gov.dvsa.ui.pages.mot.EnforcementTestSummaryPage;
import uk.gov.dvsa.ui.pages.mot.StartTestConfirmationPage;
import uk.gov.dvsa.ui.pages.mot.certificates.*;

import java.io.IOException;
import java.net.URISyntaxException;

public class Certificate {

    private PageNavigator pageNavigator;
    DuplicateReplacementCertificateTestHistoryPage certificateTestHistoryPage;
    MotTestCertificatePage certificatePage;
    ReplacementCertificateUpdatePage updatePage;
    ReplacementCertificateReviewPage reviewPage;
    CertificateEditUpdatePage editSuccessfulPage;
    private boolean isPrintButtonDisplayed;

    public Certificate(PageNavigator pageNavigator) { this.pageNavigator = pageNavigator; }

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
}
