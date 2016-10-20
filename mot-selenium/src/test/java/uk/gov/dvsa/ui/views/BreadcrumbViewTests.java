package uk.gov.dvsa.ui.views;

import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.HomePage;
import uk.gov.dvsa.ui.pages.vts.VehicleTestingStationPage;

import java.io.IOException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.containsString;

public class BreadcrumbViewTests extends DslTest {

    private User tester;
    AeDetails aeDetails;

    @BeforeMethod(alwaysRun = true)
    private void setup() throws IOException {
        aeDetails = aeData.createAeWithDefaultValues();
        Site site = siteData.createNewSite(aeDetails.getId(), "default-site");
        tester = motApi.user.createTester(site.getId());
    }

    @Test(groups = {"Regression"}, description = "Breadcrumb is visible in vehicle testing station")
    public void isBreadcrumbVisibleInVehicleTestingStationPage() throws Exception {
        //Given I am logged in as tester and I am on the Vehicle testing station page
        VehicleTestingStationPage vehicleTestingStationPage = pageNavigator.navigateToPage(tester, HomePage.PATH, HomePage.class)
                .selectRandomVts();

        //Then the breadcrumbNames are displayed in breadcrumb top navigation panel
        assertThat(vehicleTestingStationPage.getPageHeader(), containsString(aeDetails.getAeName()));
    }
}
