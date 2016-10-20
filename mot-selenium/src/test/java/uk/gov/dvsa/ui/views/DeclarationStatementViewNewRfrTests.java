package uk.gov.dvsa.ui.views;

import org.joda.time.DateTime;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.api.response.Vehicle;
import uk.gov.dvsa.ui.DslTest;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class DeclarationStatementViewNewRfrTests extends DslTest {

    private Site site;
    private User tester;
    private Vehicle vehicle;

    @BeforeMethod(alwaysRun = true)
    private void setupTestData() throws IOException {
        AeDetails aeDetails = aeData.createAeWithDefaultValues();
        site = siteData.createNewSite(aeDetails.getId(), "TestSite");
        tester = motApi.user.createTester(site.getId());
        vehicle = vehicleData.getNewVehicle(tester);
    }

    @Test(testName = "TestResultEntryImprovements", groups = {"Regression"})
    public void displayStatementAtContingencySummaryPage() throws IOException, URISyntaxException {

        //Given I start a contingency test
        motUI.contingency.testPage(tester);

        //When I complete a contingency test and view the summary page
        motUI.contingency.recordTest("12345A", DateTime.now().minusHours(1), vehicle);

        //Then I should be presented with the declaration statement
        assertThat(motUI.contingency.isDeclarationStatementDisplayed(), is(true));
    }
}