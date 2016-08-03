package uk.gov.dvsa.ui.feature.journey.mot;

import org.testng.annotations.Test;
import uk.gov.dvsa.ui.DslTest;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.containsString;

public class ReasonForRejectionTests extends DslTest {

    private static final String PROFANITY_MESSAGE = "Profanity has been detected in the description of RFR";

    @Test(testName = "OldRFRTest", groups = {"Regression", "VM-1581", "VM-1578", "VM-1579", "short-vehicle", "VM-1741"})
    public void rejectWithProfanityAddedToDescription() throws IOException, URISyntaxException {
        //Given I start an mot test
        motUI.normalTest.startTest();

        //When I add a manual advisory with profanity description
        String validationMessage = motUI.normalTest.addManualAdvisoryWithProfaneDescription("a$$hole$");

        //Then I should get profanity validation message
        assertThat(validationMessage, containsString(PROFANITY_MESSAGE));
    }
}
