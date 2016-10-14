package uk.gov.dvsa.ui.feature.journey.mot;

import org.joda.time.DateTime;
import org.joda.time.DateTimeZone;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.mot.TestOutcome;
import uk.gov.dvsa.domain.api.response.Vehicle;
import uk.gov.dvsa.ui.DslTest;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class ContingencyMotTests extends DslTest {
    private User tester;
    private Vehicle vehicle;
    private final String contingencyCode = "12345A";
    private AeDetails aeDetails;
    private Site site;
    private User siteManager;

    @BeforeMethod(alwaysRun = true)
    private void setup() throws IOException {
        aeDetails = aeData.createAeWithDefaultValues();
        site = siteData.createNewSite(aeDetails.getId(), "New_vts");
        tester = userData.createTester(site.getId());
        siteManager = userData.createSiteManager(site.getId(), true);
        vehicle = vehicleData.getNewVehicle(tester);
    }

    @Test(groups = {"BVT", "VM-4825,Sprint05,VM-9444"})
    public void recordContingencyTestSuccessfully() throws IOException, URISyntaxException {
        //Given I am the Record Contingency Page
        motUI.contingency.testPage(userData.createTester(site.getId()));

        //When I enter valid Contingency Test details
        motUI.contingency.recordTest(contingencyCode, DateTime.now().minusHours(1), vehicle);

        //Then it Contingency is entered successfully
        assertThat(motUI.contingency.isTestSaveSuccessful(), is(true));
    }

    @Test(groups = {"BVT", "VM-4825,Sprint05,VM-9444"})
    public void conductReTestSuccessfully() throws IOException, URISyntaxException {
        //Given I have a vehicle with a failed MOT test
        motApi.createTest(tester, site.getId(), vehicle, TestOutcome.FAILED, 12345, DateTime.now().minusMinutes(30));

        //And all faults has been fixed

        //When I Conduct a re-test on the vehicle via contingency route
        motUI.contingency.testPage(tester);
        motUI.contingency.recordReTest(contingencyCode, DateTime.now().minusMinutes(10).withZone(DateTimeZone.UTC), vehicle);

        //Then the retest is successful
        motUI.contingency.isTestSaveSuccessful();
    }
}
