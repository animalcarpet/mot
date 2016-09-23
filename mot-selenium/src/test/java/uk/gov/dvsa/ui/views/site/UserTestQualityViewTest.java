package uk.gov.dvsa.ui.views.site;

import org.joda.time.DateTime;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.mot.TestOutcome;
import uk.gov.dvsa.domain.model.vehicle.VehicleClass;
import uk.gov.dvsa.helper.ReasonForRejection;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.vts.SiteTestQualityPage;
import uk.gov.dvsa.ui.pages.vts.UserTestQualityPage;

import java.io.IOException;
import java.net.URISyntaxException;
import java.util.ArrayList;
import java.util.List;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class UserTestQualityViewTest extends DslTest {
    private static final int MILEAGE = 14000;
    private User tester;
    private Site site;
    private AeDetails ae;

    @BeforeMethod(alwaysRun = true)
    private void setup() throws IOException {
        ae = aeData.createNewAe("TestQuality AE", 100);
        site = siteData.createNewSite(ae.getId(), "TestQuality Site");
        tester = userData.createTester(site.getId());
    }

    @Test(groups = {"Regression"}, description = "Verifies that tester can view Test Quality for site")
    public void viewUserTestQualityForGroupA() throws IOException, URISyntaxException {
        //National stats calculations are cached
        siteData.clearAllCachedStatistics();

        //Given there are tests created for site in previous month
        List<ReasonForRejection> rfrList = new ArrayList<>();
        rfrList.add(ReasonForRejection.WARNING_LAMP_MISSING);

        motApi.createTestWithRfr(tester, site.getId(),
            vehicleData.getNewVehicle(tester, VehicleClass.one), TestOutcome.FAILED, MILEAGE, getFirstDayOfPreviousMonth(), rfrList);

        //When I go to site Test Quality page
        SiteTestQualityPage siteTestQualityPage = motUI.site.gotoTestQuality(tester, site);

        //Then tester statistics are displayed
        UserTestQualityPage userTestQualityPage = siteTestQualityPage.goToUserTestQualityPageForGroupA(tester.getUsername());
        assertThat("Test count is correct", userTestQualityPage.getTestCount(), is(1));
        //And return link is displayed
        assertThat("Return link is displayed", userTestQualityPage.isReturnLinkDisplayed(), is(true));
        //And correct average is calculated
        assertThat("Correct average is displayed", userTestQualityPage.testerAverageEquals("Motorcycle lighting and signalling", 100), is(true));
    }

    @Test(groups = {"Regression"}, description = "Verifies that tester can view Test Quality for site")
    public void viewUserTestQualityForGroupB() throws IOException, URISyntaxException {
        //National stats calculations are cached
        siteData.clearAllCachedStatistics();

        //Given there are tests created for site in previous month
        List<ReasonForRejection> rfrList = new ArrayList<>();
        rfrList.add(ReasonForRejection.HORN_CONTROL_MISSING);

        motApi.createTestWithRfr(tester, site.getId(),
            vehicleData.getNewVehicle(tester, VehicleClass.four), TestOutcome.FAILED, MILEAGE, getFirstDayOfPreviousMonth(), rfrList);

        //When I go to site Test Quality page
        SiteTestQualityPage siteTestQualityPage = motUI.site.gotoTestQuality(tester, site);

        //Then tester statistics are displayed
        UserTestQualityPage userTestQualityPage = siteTestQualityPage.goToUserTestQualityPageForGroupB(tester.getUsername());
        assertThat("Test count is correct", userTestQualityPage.getTestCount(), is(1));
        //And return link is displayed
        assertThat("Return link is displayed", userTestQualityPage.isReturnLinkDisplayed(), is(true));
        //And correct average is calculated
        assertThat("Correct average is displayed", userTestQualityPage.testerAverageEquals("Lamps, Reflectors and Electrical Equipment", 100), is(true));
    }

    private DateTime getFirstDayOfPreviousMonth() {
        return DateTime.now().dayOfMonth().withMinimumValue().minusMonths(1);
    }
}
