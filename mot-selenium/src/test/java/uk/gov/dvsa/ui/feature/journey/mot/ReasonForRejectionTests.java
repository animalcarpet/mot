package uk.gov.dvsa.ui.feature.journey.mot;

import org.testng.annotations.Test;
import uk.gov.dvsa.ui.DslTest;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.is;

public class ReasonForRejectionTests extends DslTest {

    @Test(groups = {"Regression", "VM-1581", "VM-1578", "VM-1579", "short-vehicle", "VM-1741"})
    public void rejectWithProfanityAddedToDescription() throws IOException, URISyntaxException {
        //Given I start an mot test
        motUI.normalTest.startTest();

        //When I add a manual advisory with profanity description

        //Then I should get profanity validation message
        assertThat(motUI.normalTest.addManualAdvisoryWithProfaneDescriptionReturnsWarning("a$$hole$"), is(true));
    }
}
