package uk.gov.dvsa.journey;

import uk.gov.dvsa.domain.api.response.Vehicle;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.navigation.PageNavigator;
import uk.gov.dvsa.ui.pages.mot.StartTestConfirmationPage;
import uk.gov.dvsa.ui.pages.mot.TestCompletePage;
import uk.gov.dvsa.ui.pages.mot.TestResultsEntryNewPage;

import java.io.IOException;

public class NonMotInspection {

    private PageNavigator pageNavigator;

    public NonMotInspection(PageNavigator pageNavigator) {
        this.pageNavigator = pageNavigator;
    }

    public TestResultsEntryNewPage startNonMotInspection(User user, Vehicle vehicle) throws IOException {
        return pageNavigator.gotoHomePage(user)
                        .clickStartNonMotButton()
                        .searchVehicle(vehicle)
                        .selectVehicle(StartTestConfirmationPage.class)
                        .clickStartMotTest()
                        .clickEnterTestResultsButton();
    }

    public TestCompletePage completeNonMotInspectionWithPassValues(String siteNumber, TestResultsEntryNewPage page) {
        return page.addOdometerReading(100000)
                        .clickReviewTestButton()
                        .fillSiteIdInput(siteNumber)
                        .clickFinishButton(TestCompletePage.class);
    }
}
