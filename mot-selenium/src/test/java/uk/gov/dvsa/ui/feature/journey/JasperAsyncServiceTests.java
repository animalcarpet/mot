package uk.gov.dvsa.ui.feature.journey;

import org.testng.SkipException;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.vehicle.Vehicle;
import uk.gov.dvsa.domain.service.FeaturesService;
import uk.gov.dvsa.helper.AssertionHelper;
import uk.gov.dvsa.helper.ConfigHelper;
import uk.gov.dvsa.ui.BaseTest;
import uk.gov.dvsa.ui.pages.vts.VehicleTestingStationPage;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class JasperAsyncServiceTests extends BaseTest {
    FeaturesService service = new FeaturesService();

    @Test(groups = {"BVT", "Regression"})
    public void showAsyncHeaderOnHomePage() throws IOException {
        ConfigHelper.isJasperAsyncEnabled();

        //When I view my HomePage as a tester
        VehicleTestingStationPage vehicleTestingStationPage = pageNavigator.gotoHomePage(userData.createTester(1))
                .selectRandomVts();

        //And the MOT test certificates Link
        assertThat(vehicleTestingStationPage.isMotTestRecentCertificatesLink(), is(true));
    }

    @Test(groups = {"BVT", "Regression"})
    public void showAsyncSummaryPageAndCertificateListTest() throws IOException, URISyntaxException {
        ConfigHelper.isJasperAsyncEnabled();

        //When I perform an MOT test as a tester
        User tester = userData.createTester(1);
        Vehicle vehicle = vehicleData.getNewVehicle(tester);

        motUI.normalTest.conductTestPass(tester, vehicle);

        //Then I should see the Mot Certificate Link on the test complete page
        AssertionHelper.assertValue(motUI.normalTest.isMotCertificateLinkDisplayed(), true);

        //And I can click the Mot certificate Link to the Certificates page.
        motUI.normalTest.certificatePage();
    }
}
