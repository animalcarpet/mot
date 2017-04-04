package uk.gov.dvsa.ui.feature.journey.roles;

import org.joda.time.DateTime;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.mot.TestOutcome;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.HomePage;
import uk.gov.dvsa.ui.pages.PerformanceDashBoardPage;

import java.io.IOException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.IsEqual.equalTo;

public class PerformTesterFunctionTests extends DslTest {

    private User tester;
    private Site testSite;
    private AeDetails aeDetails;

    @BeforeMethod(alwaysRun = true)
    private void setUp() throws IOException {
        aeDetails = aeData.createNewAe("My_Test_AE", 100);
        testSite = siteData.createNewSite(aeDetails.getId(), "Test_Site");
        tester = motApi.user.createTester(testSite.getId());
    }

    @Test (groups = {"roles"})
    public void viewPerformanceDashboard() throws Exception {

        //Given I have done only 1 mot test
        motApi.createTest(tester, testSite.getId(),
                vehicleData.getNewVehicle(tester), TestOutcome.PASSED, 14000, DateTime.now());

        //When I navigate to my performance dashboard page
        PerformanceDashBoardPage performanceDashBoardPage =
                pageNavigator.navigateToPage(tester, PerformanceDashBoardPage.PATH, PerformanceDashBoardPage.class);

        //Then I should see my test conducted is 1
        assertThat(performanceDashBoardPage.getTestConductedText(), equalTo("1"));

        //And passed test should be 1
        assertThat(performanceDashBoardPage.getPassedTestText(), equalTo("1"));
    }

    @Test (groups = {"roles"})
    public void verifyTheCorrectAeAndVtsIsDisplayed() throws Exception {

        //Given I am logged as a Tester and I am on my homepage
        HomePage homePage = pageNavigator.navigateToPage(tester, HomePage.PATH, HomePage.class);

        //Then my AE should be My Test AE
        assertThat(homePage.getAeName(), equalTo( aeDetails.getAeName()));

        //And my Vts should be Test Site
        assertThat(homePage.getSiteName(), equalTo(testSite.getSiteNameAndNumberInHomePageFormat()));
    }

    @Test (groups = {"roles"})
    public void myRoleIsDisplayedAsTester() throws Exception {

        //Given I am logged as a Tester and I am on my homepage
        HomePage homePage = pageNavigator.navigateToPage(tester, HomePage.PATH, HomePage.class);

        //I expect to see my role displayed
        assertThat(homePage.getRole(), equalTo("Tester".toUpperCase()));
    }
}
