package uk.gov.dvsa.ui.feature.journey.mot;

import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.ui.DslTest;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.containsString;

public class VehicleTestingAdviceTests extends DslTest {

    private User tester;

    @BeforeMethod(alwaysRun = true)
    public void setUp() throws IOException {
        Site site = siteData.createSite();
        tester = motApi.user.createTester(site.getId());
    }

    @Test(groups = {"BVT"},
            description = "Tester can read the vehicle testing advice")
    public void testerCanReadVehicleTestingAdvice() throws IOException, URISyntaxException {

        step("Given I am logged into MOT2 as a Tester");
        step("And I select a vehicle to start a MOT test");
        motUI.normalTest.startTestConfirmationPage(tester, vehicleData.getNewVehicleWithTestingAdvice(tester));

        step("When a vehicle has testing advice");
        step("Then I should be able see testing advice");
        motUI.normalTest.goToVehicleTestingAdvice();
    }
}
