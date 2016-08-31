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

public class AggregetedTestQualityViewTests extends DslTest {

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
        ao1 = userData.createAreaOfficeOne("ao1");
        ao2 = userData.createAreaOfficeOne("ao2");
        site = siteData.createNewSite(aeDetails.getId(), "default-site-tqi1");
        tester = userData.createTester(site.getId());
        tester2 = userData.createTester(site.getId());
        tester3 = userData.createTester(site.getId());
    }

    @Test(groups = {"Regression"}, description = "Verifies that user can see his own TQI page")
    public void userCanSeeHisOwnTqiPage() throws IOException, URISyntaxException {
        //Given I have performed MOT test in previous months
        DateTime fourMonthsAgo = getFirstDayOfMonth(4);

        generatePassedMotTestForThePast(fourMonthsAgo, VehicleClass.four, tester, site);

        //When I go to my Test Quality Information page
        AggregatedTestQualityPage aggregatedTqiPage = motUI.profile.viewYourProfile(tester)
                .clickTestQualityInformationLink()
                .chooseMonth(fourMonthsAgo);

        //Then it contains correct informations
        assertThat("Group A table is displayed", aggregatedTqiPage.isTableForGroupADisplayed(), is(true));
        assertThat("Group A table has 2 rows", aggregatedTqiPage.getTableForGroupARowCount(), is(2));
        assertThat("Group B table is displayed", aggregatedTqiPage.isTableForGroupBDisplayed(), is(true));
        assertThat("Group B table has 2 rows", aggregatedTqiPage.getTableForGroupBRowCount(), is(2));
        assertThat("Return link is displayed", aggregatedTqiPage.isReturnLinkDisplayed(), is(true));
    }

    @Test(groups = {"Regression"},
            description = "Verifies that DVSA user can see aggregated component breakdown TQI page of a user")
    public void dvsaUserCanSeeTesterComponentBreakdownPage() throws IOException, URISyntaxException {
        //Given user have performed MOT test in previous months
        DateTime twoMonthsAgo = getFirstDayOfMonth(2);

        generatePassedMotTestForThePast(twoMonthsAgo, VehicleClass.one, tester2, site);

        //When I go to his Test Quality Information page
        AggregatedComponentBreakdownPage componentBreakdownPage = motUI.profile.dvsaViewUserProfile(ao1, tester2)
                .clickTestQualityInformationLink()
                .chooseMonth(twoMonthsAgo)
                .clickGroupAFailures();

        //Then it contains correct informations
        assertThat("Return link is displayed", componentBreakdownPage.isReturnLinkDisplayed(), is(true));
        assertThat("Test count is correct", componentBreakdownPage.getTestCount(), is(1));
    }

    @Test(groups = {"Regression"},
            description = "Verifies that DVSA user can see component breakdown for tester at site from person TQI journey")
    public void dvsaUserCanSeeTesterAtSiteComponentBreakdownPage() throws IOException, URISyntaxException {
        //Given user have performed MOT test in previous months
        DateTime threeMonthsAgo = getFirstDayOfMonth(3);

        generatePassedMotTestForThePast(threeMonthsAgo, VehicleClass.one, tester3, site);

        //When I go to his Test Quality Information page
        TesterAtSiteComponentBreakdownPage componentBreakdownPage = motUI.profile.dvsaViewUserProfile(ao2, tester3)
                .clickTestQualityInformationLink()
                .chooseMonth(threeMonthsAgo)
                .clickFirstSiteInGroupAFailures();

        //Then it contains correct informations
        assertThat("Return link is displayed", componentBreakdownPage.isReturnLinkDisplayed(), is(true));
        assertThat("Test count is correct", componentBreakdownPage.getTestCount(), is(1));
    }

    private MotTest generatePassedMotTestForThePast(DateTime date, VehicleClass vehicleClass, User tester, Site site) throws IOException {
        return motApi.createTest(tester, site.getId(),
                vehicleData.getNewVehicle(tester, vehicleClass), TestOutcome.PASSED, MILEAGE, date);
    }

    private DateTime getFirstDayOfMonth(int monthsAgo) {
        return DateTime.now().dayOfMonth().withMinimumValue().minusMonths(monthsAgo);
    }
}
