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

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class AggregetedTestQualityViewTests extends DslTest {

    private static final int MILEAGE = 14000;
    private User tester;
    private Site site;
    private User ao1;

    @BeforeClass(alwaysRun = true)
    private void setup() throws IOException {
        AeDetails aeDetails = aeData.createAeWithDefaultValues();
        site = siteData.createNewSite(aeDetails.getId(), "default-site");
        ao1 = userData.createAreaOfficeOne("ao1");
        tester = userData.createTester(site.getId());
    }

    @Test(groups = {"Regression"}, description = "Verifies that user can see his own TQI page")
    public void userCanSeeHisOwnTqiPage() throws IOException, URISyntaxException {
        //Given I have performed MOT test in previous months
        DateTime fourMonthsAgo = getFirstDayOfMonth(4);
        generatePassedMotTestForThePast(fourMonthsAgo, VehicleClass.four);

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
    public void dvsaUserCanSeeTesterTqiComponentBreakedownPage() throws IOException, URISyntaxException {
        //Given user have performed MOT test in previous months
        DateTime twoMonthsAgo = getFirstDayOfMonth(2);
        generatePassedMotTestForThePast(twoMonthsAgo, VehicleClass.one);

        //When I go to his Test Quality Information page
        AggregatedComponentBreakdownPage componentBreakdownPage = motUI.profile.dvsaViewUserProfile(ao1, tester)
            .clickTestQualityInformationLink()
            .chooseMonth(twoMonthsAgo)
            .clickGroupAFailures();

        //Then it contains correct informations
        assertThat("Return link is displayed", componentBreakdownPage.isReturnLinkDisplayed(), is(true));
        assertThat("Test count is correct", componentBreakdownPage.getTestCount(), is(1));
    }

    private MotTest generatePassedMotTestForThePast(DateTime date, VehicleClass vehicleClass) throws IOException {
        return motApi.createTest(tester, site.getId(),
            vehicleData.getNewVehicle(tester, vehicleClass), TestOutcome.PASSED, MILEAGE, date);
    }

    private DateTime getFirstDayOfMonth(int monthsAgo) {
        return DateTime.now().dayOfMonth().withMinimumValue().minusMonths(monthsAgo);
    }
}
