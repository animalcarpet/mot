package uk.gov.dvsa.ui.views.site;

import org.joda.time.DateTime;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.mot.TestOutcome;
import uk.gov.dvsa.domain.model.vehicle.VehicleClass;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.vts.SiteTestQualityPage;

import java.io.IOException;
import java.net.URISyntaxException;
import java.util.Calendar;
import java.util.Date;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class SiteTestQualityViewTests extends DslTest {
    private User tester;
    private Site site;
    private AeDetails ae;

    @BeforeMethod(alwaysRun = true)
    private void setup() throws IOException {
        ae = aeData.createNewAe("TestQuality AE", 100);
        site = siteData.createSiteWithStartSiteOrgLinkDate(ae.getId(), "TestQuality Site", new DateTime(this.getFirstDayOfMonth(13)));
        tester = userData.createTester(site.getId());
    }

    @Test(testName = "TestQualityInformation", groups = {"Regression"},
        description = "Verifies that tester can view Test Quality for site")
    public void viewSiteTestQuality() throws IOException, URISyntaxException {
        //National stats calculations are cached
        siteData.clearAllCachedStatistics();

        //Given there are tests created for site in previous month
        DateTime date = new DateTime(this.getFirstDayOfMonth(1));
        motApi.createTest(tester, site.getId(),
            vehicleData.getNewVehicle(tester, VehicleClass.one), TestOutcome.PASSED, 14000, date);
        motApi.createTest(tester, site.getId(),
            vehicleData.getNewVehicle(tester, VehicleClass.four), TestOutcome.PASSED, 14000, date);

        //When I go to site Test Quality page
        SiteTestQualityPage siteTestQualityPage = motUI.site.gotoTestQuality(tester, site);

        //Then tester statistics are displayed
        assertThat("Group A table is displayed", siteTestQualityPage.isTableForGroupADisplayed(), is(true));
        assertThat("Group A table has 2 rows", siteTestQualityPage.getTableForGroupARowCount(), is(2));
        assertThat("Group B table is displayed", siteTestQualityPage.isTableForGroupBDisplayed(), is(true));
        assertThat("Group B table has 2 rows", siteTestQualityPage.getTableForGroupBRowCount(), is(2));

        //And return link is displayed
        assertThat("Return link is displayed", siteTestQualityPage.isReturnLinkDisplayed(), is(true));
    }

    @Test(testName = "TestQualityInformation", groups = {"Regression"},
        description = "Verifies that tester can view Test Quality for site with a link to 12 months ago and correct data")
    public void checkDataForTwelveMonthsAgo() throws IOException, URISyntaxException {
        //National stats calculations are cached
        siteData.clearAllCachedStatistics();

        //Given there are tests created for site in 12 months ago
        DateTime date = new DateTime(this.getFirstDayOfMonth(12));
        motApi.createTest(tester, site.getId(),
            vehicleData.getNewVehicle(tester, VehicleClass.one), TestOutcome.FAILED, 14000, date);
        motApi.createTest(tester, site.getId(),
            vehicleData.getNewVehicle(tester, VehicleClass.four), TestOutcome.FAILED, 14000, date);

        //When I go to site Test Quality page
        SiteTestQualityPage siteTestQualityPage = motUI.site.gotoTestQuality(tester, site)
            .chooseMonth(date)
            .waitUntilPageTertiaryTitleWillShowDate(date);

        //Then there no exist link for previous month (13 months ago)
        assertThat("Return link is displayed", siteTestQualityPage.isThirteenMonthsAgoLinkPresent(), is(false));

        //And tester statistics are displayed
        assertThat("Group A table is displayed", siteTestQualityPage.isTableForGroupADisplayed(), is(true));
        assertThat("Group A table has 2 rows", siteTestQualityPage.getTableForGroupARowCount(), is(2));
        assertThat("Group B table is displayed", siteTestQualityPage.isTableForGroupBDisplayed(), is(true));
        assertThat("Group B table has 2 rows", siteTestQualityPage.getTableForGroupBRowCount(), is(2));

        //And return link is displayed
        assertThat("Return link is displayed", siteTestQualityPage.isReturnLinkDisplayed(), is(true));
    }

    private Date getFirstDayOfMonth(int monthsAgo) {
        Calendar calendar = Calendar.getInstance();
        calendar.add(Calendar.MONTH, (-1 * monthsAgo));
        calendar.set(Calendar.DATE, 1);

        return calendar.getTime();
    }
}