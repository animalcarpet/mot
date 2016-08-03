package uk.gov.dvsa.ui.feature.journey.account_administration;

import org.joda.time.DateTime;
import org.testng.annotations.DataProvider;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.AeContactDetails;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.mot.TestOutcome;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.HomePage;
import uk.gov.dvsa.ui.pages.authorisedexaminer.*;
import uk.gov.dvsa.ui.pages.vts.VehicleTestingStationPage;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.containsString;
import static org.hamcrest.core.Is.is;
import static org.hamcrest.core.IsEqual.equalTo;

public class AedmManagesMotAccountTests extends DslTest {

    @Test(groups = {"Regression"},
            description = "VM-10253 - Journey 2 - AEDM Manages Mot Account", dataProvider = "createAeAedmSiteAndTester")
    public void viewConnectedAEWithAttachedVtsAndTester(
            User user, AeDetails aeDetails, Site testSite, User tester) throws Exception {

        //Given I am on my Homepage as AEDM
        HomePage homePage = pageNavigator.navigateToPage(user, HomePage.PATH, HomePage.class);

        //I expect to see all AE's I am connected with and their respective VTS's
        assertThat(homePage.getAeName(), equalTo(aeDetails.getAeName()));
        assertThat(homePage.getSiteName(), equalTo(testSite.getSiteNameAndNumberInHomePageFormat()));

        //When I select a VTS
        VehicleTestingStationPage vehicleTestingStationPage= homePage.selectRandomVts();

        //Then I should see all testers in that VTS
        assertThat(vehicleTestingStationPage.getTesterName(tester.getId()), equalTo(tester.getNamesAndSurname()));
    }

    @Test(groups = {"Regression"},
            description = "VM-10255 - Journey 2 - AEDM View AE Test logs", dataProvider = "createAeAedmSiteAndTester")
    public void viewAETestLogs(User aedm, AeDetails aeDetails, Site site, User tester) throws IOException, URISyntaxException {

        //Given I perform an MOT test for my selected Authorised Examiner
        motApi.createTest(tester, site.getId(),
                vehicleData.getNewVehicle(tester), TestOutcome.PASSED, 14000, DateTime.now());

        //When I navigate to the Authorised Examiner Test Logs page
        AuthorisedExaminerTestLogPage authorisedExaminerPageTestLogPage =
                pageNavigator.goToPageAsAuthorisedExaminer(aedm, AuthorisedExaminerTestLogPage.class, AuthorisedExaminerTestLogPage.PATH, aeDetails.getId());

        //Then Today's Test count should be equal to number of test for that AE
        assertThat(authorisedExaminerPageTestLogPage.getTodayCount(), equalTo("1"));
    }

    @Test(groups = {"Regression"},
            description = "VM-10255 - Journey 2 - AEDM View AE Slot Usage", dataProvider = "createAeAedmSiteAndTester")
    public void viewSlotReport(User aedm, AeDetails aeDetails, Site site, User tester) throws IOException, URISyntaxException {

        //Given I perform an MOT test for my selected Authorised Examiner
        motApi.createTest(tester, site.getId(),
                vehicleData.getNewVehicle(tester), TestOutcome.PASSED, 14000, DateTime.now());

        //When I navigate to the Slots usage Page
        AeSlotsUsagePage aeSlotsUsagePage = pageNavigator.goToPageAsAuthorisedExaminer(aedm, AeSlotsUsagePage.class, AeSlotsUsagePage.PATH, aeDetails.getId());

        //Then the Slots Usage table should contain number of test done
        assertThat(aeSlotsUsagePage.getSlotUsageCountMessage(), containsString("1 slot used today"));
    }

    @Test(groups = {"Regression"},
            description = "VM-10257 - Journey 2 - AEDM Remove Tester from VTS", dataProvider = "createAedmTester")
    public void removeTesterFromVTS(User user, User tester) throws Exception {

        //Given I am on the vehicle testing page
        VehicleTestingStationPage vehicleTestingStationPage = pageNavigator.navigateToPage(user, HomePage.PATH,  HomePage.class)
                .selectRandomVts();

        //When I remove a tester from the VTS
        vehicleTestingStationPage.removeTesterRole(tester.getId()).confirmRemoveRole();

        //Then the tester should no longer be associated to Vts
        assertThat(vehicleTestingStationPage.isTesterDisplayed(tester.getId()), is(false));
    }

    @DataProvider(name = "createAedmSite")
    public Object[][] createAedmSite() throws IOException {
        AeDetails aeDetails = aeData.createAeWithDefaultValues();
        User aedm = userData.createAedm(aeDetails.getId(), "My_AEDM", false);
        Site site = siteData.createNewSite(aeDetails.getId(), "My_TestSite");

        return new Object[][]{{aedm, site}};
    }

    @DataProvider(name = "createAedmTester")
    public Object[][] createAedmTester() throws IOException {
        AeDetails aeDetails = aeData.createAeWithDefaultValues();
        User aedm = userData.createAedm(aeDetails.getId(), "My_AEDM", false);
        Site site = siteData.createNewSite(aeDetails.getId(), "My_TestSite");
        User tester = userData.createTester(site.getId());

        return new Object[][]{{aedm, tester}};
    }

    @DataProvider(name = "createAeAedmSiteAndTester")
    public Object[][] createAeAedmSiteAndTester() throws IOException {
        AeDetails aeDetails = aeData.createAeWithDefaultValues();
        Site testSite = siteData.createNewSite(aeDetails.getId(), "My_Site");
        User aedm = userData.createAedm(aeDetails.getId(), "My_AEDM", false);
        User tester = userData.createTester(testSite.getId());

        return new Object[][]{{aedm, aeDetails, testSite, tester}};
    }

    AeContactDetails aeContactDetails = new AeContactDetails("dummy@email.com", "dummy@email.com", "0117832934");
}
