package uk.gov.dvsa.ui.feature.journey.mot.reinspection;

import org.joda.time.DateTime;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.mot.MotTest;
import uk.gov.dvsa.domain.model.mot.TestOutcome;
import uk.gov.dvsa.domain.model.vehicle.Vehicle;
import uk.gov.dvsa.domain.shared.role.DvsaRoles;
import uk.gov.dvsa.domain.shared.role.RoleManager;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.HomePage;
import uk.gov.dvsa.ui.pages.events.EventsHistoryPage;
import uk.gov.dvsa.ui.pages.mot.TestShortSummaryPage;
import uk.gov.dvsa.ui.pages.vts.VehicleTestingStationPage;

import java.io.IOException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.containsString;
import static org.hamcrest.core.Is.is;
import static uk.gov.dvsa.domain.model.mot.MotTestSearchCategory.REGISTRATION;

public class ReInspectionTests extends DslTest {
    private User vehicleExaminer;
    private User tester;
    private Site testSite;

    @BeforeMethod(alwaysRun = true)
    private void setup() throws IOException {
        vehicleExaminer = userData.createVehicleExaminer("ft-Enf-", false);
        AeDetails aeDetails = aeData.createNewAe("VeReinspectionTest", 8);
        testSite = siteData.createNewSite(aeDetails.getId(), "Test_Site");
        tester = userData.createTester(testSite.getId());
    }

    @Test(groups = {"BVT"}, description = "VM-9858, Journey 5")
    public void veConductsVehicleReInspectionAndRecordsTheResultOfTheDvsaTest() throws IOException {

        //Given a vehicle failed Mot
        String motTestId = motApi.createTest(
                tester,
                testSite.getId(), vehicleData.getNewVehicle(tester), TestOutcome.FAILED, 123456,
                DateTime.now()).getMotTestNumber();

        //When I conduct a re-inspection as VE
        String message =
                motUI.reInspection.targetedReInspection(userData.createVehicleExaminer("ft-Enf-", false), motTestId);

        //Then I should be able to record the assessment successfully
        assertThat("Assessment is recorded successfully", message, containsString("the test differences have been saved"));
    }

    @Test(groups = {"BVT"}, description = "VM-9858, Journey 5")
    public void veUsersCanFindAndViewEventHistory() throws IOException {

        //Given that I'm logged in as a VE, and I go to the Events History page
        EventsHistoryPage eventsHistoryPage =
                vehicleReinspectionWorkflow().gotoEventsHistoryPage(vehicleExaminer, testSite.getIdAsString());

        //I can view events history of VTS
        assertThat(eventsHistoryPage.isListOfEventsDisplayed(), is(true));
    }

    @Test(groups = {"BVT"}, description = "VM-9858, Journey 5")
    public void veCanViewBasicVtsContactDetails() throws IOException {

        //Given that I'm logged in as a VE, and I go to Vehicle Testing Station page
        VehicleTestingStationPage vehicleTestingStationPage =
                vehicleReinspectionWorkflow().gotoVehicleTestingStationPage(vehicleExaminer, testSite.getIdAsString());

        //I can view vts contact detail to plan a visit
        assertThat(vehicleTestingStationPage.isAddressDisplayed(), is(true));
        assertThat(vehicleTestingStationPage.isEmailDisplayed(), is(true));
        assertThat(vehicleTestingStationPage.isPhoneNumberDisplayed(), is(true));
    }

    @Test(groups = {"BVT"}, description = "VM-9858, Journey 5")
    public void veCanViewCurrentActivityOnVtsPageAndAbortActiveTest() throws IOException {

        //Prepare mot test in progress
        Vehicle testVehicle = vehicleData.getNewVehicle(tester);
        String testVehicleRegistration = testVehicle.getDvsaRegistration();
        vehicleReinspectionWorkflow().startMotTestAsATester(tester, testVehicle);
        pageNavigator.signOutAndGoToLoginPage();

        //Given that I'm logged in as a VE, and I go to Vehicle Testing Station page
        VehicleTestingStationPage vehicleTestingStationPage =
                vehicleReinspectionWorkflow().gotoVehicleTestingStationPage(vehicleExaminer, testSite.getIdAsString());

        //I can view active MOT Test
        assertThat(vehicleTestingStationPage.isActiveMotTestDisplayed(testVehicleRegistration), is(true));

        //And I can abort active MOT Test
        TestShortSummaryPage testShortSummaryPage =
                vehicleReinspectionWorkflow().abortActiveTestOnVtsPage(testVehicleRegistration);

        assertThat(testShortSummaryPage.isTestAbortedSuccessfull(), is(true));
    }

    @Test(groups = {"BVT"}, description = "BL-101")
    public void userWithVeAndA01RolesCanResumeReInspection() throws IOException {

        //Given I am a User with a AO1 and VE Role
        User ao1PlusVe = userData.createAreaOfficeOne("AO1PlusVE");
        Vehicle vehicle = vehicleData.getNewVehicle(tester);
        RoleManager.addSystemRole(ao1PlusVe, DvsaRoles.VEHICLE_EXAMINER);

        //When I start a reInspection test
        MotTest motTest = motApi.createTest(tester, testSite.getId(), vehicle,
                TestOutcome.PASSED, 123456, DateTime.now());

        vehicleReinspectionWorkflow()
                .searchForMotTest(ao1PlusVe, REGISTRATION.getValue(),
                        vehicle.getDvsaRegistration(), motTest.getMotTestNumber())
                .clickStartReinspectionButton();

        //And Return to the homepage
        HomePage homePage = pageNavigator.gotoHomePage(ao1PlusVe);

        //Then I should see the [Resume ReInspection] button
        assertThat(homePage.isResumeMotTestDisplayed(), is(true));
    }
}
