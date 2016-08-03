package uk.gov.dvsa.ui.views.site;

import org.joda.time.DateTime;
import org.testng.annotations.DataProvider;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.mot.TestOutcome;
import uk.gov.dvsa.helper.enums.DateRangeFilter;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.exception.PageInstanceNotFoundException;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class SiteTestLogsView extends DslTest {

    Site testSite;
    User tester;

    @DataProvider(name = "users")
    public Object[][] permittedUsers() throws IOException {
        AeDetails aeDetails = aeData.createAeWithDefaultValues();
        testSite = siteData.createNewSite(aeDetails.getId(), "My_Site");
        tester = userData.createTester(testSite.getId());

        return new Object[][]{
                {userData.createAedm(aeDetails.getId(), "TestAe", false)},
                {userData.createSiteAdmin(testSite.getId(), false)},
                {userData.createAreaOfficeOne("permittedAE")},
                {userData.createVehicleExaminer("permittedVE", false)}
        };
    }

    @Test(groups = {"BVT"}, dataProvider = "users")
    public void permittedUserCanViewTestLogs(User user) throws IOException, URISyntaxException {

        //Given I performed 1 MOT  test Last week
        motApi.createTest(tester, testSite.getId(), vehicleData.getNewVehicle(tester), TestOutcome.PASSED, 123456, DateTime.now().minusWeeks(1));

        //When I go to the Site Log page as <permitted user>
        motUI.testLog.siteLogPage(user, testSite.getId());

        //Then I am able to view the Test logs, with *last week's worth (Mon to Sun) as default View
        assertThat(motUI.testLog.isDisplayed(), is(true));
        assertThat("Expected Default View to be " + DateRangeFilter.LAST_WEEK , motUI.testLog.isSelected(DateRangeFilter.LAST_WEEK), is(true));

        //And I can go back to the VTS Page
        motUI.testLog.home();
    }

    @Test(groups = {"Regression"}, expectedExceptions = PageInstanceNotFoundException.class)
    public void nonPermittedUserCannotViewTestLogs() throws IOException, URISyntaxException {

        //Given I am a tester
        Site site = siteData.createSite();
        User tester = userData.createTester(site.getId());

        //When I attempt to view the VTS Test Logs
        motUI.testLog.siteLogPage(tester, site.getId());

        //Then I am not able to view the Test log
        assertThat(motUI.testLog.isDisplayed(), is(false));

    }
    
    @Test(groups = {"Regression"})
    public void permittedUserCanViewCustomDateRange() throws IOException, URISyntaxException {

        //Given I performed 2 Mot tests within 30 days
        AeDetails aeDetails = aeData.createAeWithDefaultValues();
        Site testSite = siteData.createNewSite(aeDetails.getId(), "My_Site");
        User tester = userData.createTester(testSite.getId());

        DateTime firstTestDate = DateTime.now().withDayOfMonth(1);
        DateTime secondTestDate = DateTime.now().minusDays(30);

        motApi.createTest(tester, testSite.getId(), vehicleData.getNewVehicle(tester),
                TestOutcome.PASSED, 123456, firstTestDate);

        motApi.createTest(tester, testSite.getId(), vehicleData.getNewVehicle(tester),
                TestOutcome.PASSED, 123456, secondTestDate);


        // When I go to the VTS Test Log page as <permitted user>
        motUI.testLog.siteLogPage(userData.createAreaOfficeOne("AreaOfficer"), testSite.getId());

        //When I search with a date range
        motUI.testLog.selectDateRange(firstTestDate, secondTestDate);

        //Then the data table should be displayed containing only 2 Mot test
        assertThat("The Correct number of Mot is returned", motUI.testLog.getNumberOfMotTestInTable(), is(2));
    }
}
