package uk.gov.dvsa.ui.feature.journey.account_administration;

import org.joda.time.DateTime;
import org.testng.annotations.BeforeClass;
import org.testng.annotations.DataProvider;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.vts.AssessmentInfo;

import java.io.IOException;
import java.net.URISyntaxException;
import java.util.HashMap;
import java.util.Map;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.containsString;
import static org.hamcrest.Matchers.greaterThan;
import static org.hamcrest.core.Is.is;
import static org.hamcrest.core.IsEqual.equalTo;

public class SiteRagStatusTests extends DslTest {

    private User areaOffice1User;
    private Site bobBangers;
    private User tester;
    private User vehicleExaminer;
    private Map<String, User> assessmentActors = new HashMap<>();

    @BeforeClass(alwaysRun = true)
    private void setup() throws IOException {
        areaOffice1User = motApi.user.createAreaOfficeOne("AO1");
        bobBangers = siteData.createSite("BobBangers");
        tester = motApi.user.createTester(bobBangers.getId());
        vehicleExaminer = motApi.user.createVehicleExaminer("ft-Enf-", false);

        assessmentActors.put("areaOffice1User", areaOffice1User);
        assessmentActors.put("vehicleExaminer", vehicleExaminer);
        assessmentActors.put("tester", tester);
    }

    @DataProvider(name = "assessmentValues")
    public Object[][] assessmentValues() {
        return new Object[][]{
                {new AssessmentInfo("200.1", "Green", assessmentActors)},
                {new AssessmentInfo("350", "Amber", assessmentActors)},
                {new AssessmentInfo("365", "Red", assessmentActors)}};
    }

    @Test(groups = {"Regression"}, description = "VM-11544")
    void checkStatusAsTester() throws IOException{
        //Given I am on BobBangers VTS page as a Tester in BobBangers
        motUI.site.gotoPage(tester, String.valueOf(bobBangers.getId()));

        //Then I should see the Risk Score
        assertThat(motUI.site.isRiskScoreDisplayed(), is(true));
    }

    @Test(groups = {"Regression"}, description = "VM-11544, Journey 1", dataProvider = "assessmentValues")
    void addSiteAssessmentScore(AssessmentInfo aInfo) throws IOException, URISyntaxException {
        //Given I am on the Enter Site Assessment Page as VE
        motUI.site.gotoAssessment(motApi.user.createVehicleExaminer("ve", false), String.valueOf(bobBangers.getId()));

        //When I submit assessment
        motUI.site.submitAssessment(aInfo, DateTime.now());

        //Then the assessment score is updated correctly on the VTS Page
        assertThat("Correct Score is recorded", motUI.site.getScore(), containsString(aInfo.getScore()));

        //And Colour is updated correctly on the VTS OPage
        assertThat("Correct colour value is set", motUI.site.getAssessmentColour(), equalTo(aInfo.getColor()));
    }

    @Test(groups = {"Regression"}, description = "VM-11544, Journey 1")
    void verifyEventsCreated() throws IOException, URISyntaxException {
        //Given I have an assessment added to a VTS
        motUI.site.gotoAssessment(motApi.user.createVehicleExaminer("ve", false), String.valueOf(bobBangers.getId()));
        motUI.site.submitAssessment(new AssessmentInfo("200.1", "Green", assessmentActors), DateTime.now());

        //When I check the Events history for the Vehicle Examiner
        motUI.site.gotoEventHistory(vehicleExaminer, String.valueOf(bobBangers.getId()));

        //Then I should the correct event history in the table
        assertThat("Event History is not Empty", motUI.site.getEventHistoryTableSize(), greaterThan(0));
    }
}
