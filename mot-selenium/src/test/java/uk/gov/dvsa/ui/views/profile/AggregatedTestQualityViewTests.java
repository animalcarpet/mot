package uk.gov.dvsa.ui.views.profile;

import org.joda.time.DateTime;
import org.testng.annotations.BeforeClass;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.mot.MotTest;
import uk.gov.dvsa.domain.model.mot.TestOutcome;
import uk.gov.dvsa.domain.model.vehicle.VehicleClass;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.profile.testqualityinformation.AggregatedComponentBreakdownPage;
import uk.gov.dvsa.ui.pages.profile.testqualityinformation.AggregatedTestQualityPage;
import uk.gov.dvsa.ui.pages.profile.testqualityinformation.TesterAtSiteComponentBreakdownPage;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class AggregatedTestQualityViewTests extends DslTest {

    private static final int MILEAGE = 14000;
    private User ao1;
    private AeDetails aeDetails;
    private User tester;
    private Site site;
    private User tester2;
    private User tester3;
    private User ao2;

    @BeforeClass(alwaysRun = true)
    private void setup() throws IOException {
        aeDetails = aeData.createAeWithDefaultValues();
        ao1 = motApi.user.createAreaOfficeOne("ao1");
        ao2 = motApi.user.createAreaOfficeOne("ao2");
        site = siteData.createNewSite(aeDetails.getId(), "default-site-tqi1");
        tester = motApi.user.createTester(site.getId());
        tester2 = motApi.user.createTester(site.getId());
        tester3 = motApi.user.createTester(site.getId());
    }

    @Test(groups = {"Regression"}, testName = "gqrReports3MonthsOptionDisabled", description = "Verifies that user can see his own TQI page")
    public void userCanSeeHisOwnTqiPage() throws IOException, URISyntaxException {
        //Given I have performed MOT test in previous months
        DateTime threeMonthsAgo = getFirstDayOfMonth(3);

        generatePassedMotTestForThePast(threeMonthsAgo, VehicleClass.four, tester, site);

        //When I go to my Test Quality Information page
        AggregatedTestQualityPage aggregatedTqiPage = motUI.profile.viewYourProfile(tester)
                .clickTestQualityInformationLink()
                .choose3MonthRange();

        //Then it contains correct information
        assertThat("Group A table is displayed", aggregatedTqiPage.isTableForGroupADisplayed(), is(true));
        assertThat("Group B table is displayed", aggregatedTqiPage.isTableForGroupBDisplayed(), is(true));
        assertThat("Return link is displayed", aggregatedTqiPage.isReturnLinkDisplayed(), is(true));
    }

    @Test(groups = {"Regression"},
            description = "Verifies that DVSA user can see aggregated component breakdown TQI page of a user")
    public void dvsaUserCanSeeTesterComponentBreakdownPage() throws IOException, URISyntaxException {
        //Given user have performed MOT test in previous months
        DateTime oneMonthAgo = getFirstDayOfMonth(1);

        generatePassedMotTestForThePast(oneMonthAgo, VehicleClass.one, tester2, site);

        //When I go to his Test Quality Information page
        AggregatedComponentBreakdownPage componentBreakdownPage = motUI.profile.dvsaViewUserProfile(ao1, tester2)
                .clickTestQualityInformationLink()
                .choose1MonthRange()
                .goToSiteComponentBreakdownPageForGroupA(site.getName());

        //Then it contains correct information
        assertThat("Return link is displayed", componentBreakdownPage.isReturnLinkDisplayed(), is(true));
    }

    @Test(groups = {"Regression"},
            testName = "gqrReports3MonthsOptionDisabled",
            description = "Verifies that DVSA user can see component breakdown for tester at site from person TQI journey")
    public void dvsaUserCanSeeTesterAtSiteComponentBreakdownPage() throws IOException, URISyntaxException {
        //Given user have performed MOT test in previous months
        DateTime threeMonthsAgo = getFirstDayOfMonth(3);

        generatePassedMotTestForThePast(threeMonthsAgo, VehicleClass.one, tester3, site);

        //When I go to his Test Quality Information page
        TesterAtSiteComponentBreakdownPage componentBreakdownPage = motUI.profile.dvsaViewUserProfile(ao2, tester3)
                .clickTestQualityInformationLink()
                .choose3MonthRange()
                .clickFirstSiteInGroupAFailures();

        //Then it contains correct information
        assertThat("Return link is displayed", componentBreakdownPage.isReturnLinkDisplayed(), is(true));
    }

    private MotTest generatePassedMotTestForThePast(DateTime date, VehicleClass vehicleClass, User tester, Site site) throws IOException {
        return motApi.createTest(tester, site.getId(),
                vehicleData.getNewVehicle(tester, vehicleClass), TestOutcome.PASSED, MILEAGE, date);
    }

    private DateTime getFirstDayOfMonth(int monthsAgo) {
        return DateTime.now().dayOfMonth().withMinimumValue().minusMonths(monthsAgo);
    }
}
