package com.dvsa.mot.selenium.e2e.dvsa;

import com.dvsa.mot.selenium.datasource.Assertion;
import com.dvsa.mot.selenium.datasource.Login;
import com.dvsa.mot.selenium.datasource.Person;
import com.dvsa.mot.selenium.framework.BaseTest;
import com.dvsa.mot.selenium.framework.Utilities;
import com.dvsa.mot.selenium.framework.api.*;
import com.dvsa.mot.selenium.framework.api.authorisedexaminer.AeDetails;
import com.dvsa.mot.selenium.framework.api.authorisedexaminer.AeService;
import com.dvsa.mot.selenium.framework.api.vehicle.Vm10519userCreationApi;
import com.dvsa.mot.selenium.priv.frontend.enforcement.pages.EventHistoryPage;
import com.dvsa.mot.selenium.priv.frontend.helpdesk.HelpDeskUserProfilePage;
import com.dvsa.mot.selenium.priv.frontend.helpdesk.HelpdeskUserResultsPage;
import com.dvsa.mot.selenium.priv.frontend.helpdesk.HelpdeskUserSearchPage;
import com.dvsa.mot.selenium.priv.frontend.user.QualificationChageConfirmationPage;
import com.dvsa.mot.selenium.priv.frontend.user.RecordDemoPageGroupA;
import com.dvsa.mot.selenium.priv.frontend.user.RecordDemoPageGroupB;
import com.dvsa.mot.selenium.priv.frontend.user.UserDashboardPage;
import com.dvsa.mot.selenium.pub.frontend.application.tester.pages.NotificationPage;
import org.apache.commons.lang3.RandomStringUtils;
import org.joda.time.DateTime;
import org.joda.time.format.DateTimeFormat;
import org.joda.time.format.DateTimeFormatter;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.VehicleClassGroup;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.containsString;

import java.util.ArrayList;
import java.util.Collections;

import static org.testng.Assert.assertEquals;
import static org.testng.Assert.assertFalse;
import static org.testng.Assert.assertTrue;

public class DVSAAccessUserProfileTest extends BaseTest {

    private AeService aeService = new AeService();
    private Login superVehicleExaminerUser = new Vm10519userCreationApi().createVm10519user();
    private Login vehicleExaminerUser = new VehicleExaminerUserCreationApi().createVehicleExaminerUser();

    @Test(groups = {"Regression", "VM-7647",
            "VM-10283"}, description = "Test that validates the Super Vehicle Examiner user roles can access user profiles")
    public void testProfileDetailsDisplayed() {
        Person personUser = Person.BOB_THOMAS;
        HelpDeskUserProfilePage helpDeskUserProfilePage = userSearch(personUser, superVehicleExaminerUser);

        assertEquals(helpDeskUserProfilePage.getDateOfBirth(), personUser.getDateOfBirth(),
                "Check that the date of birth is displayed");
        assertEquals(helpDeskUserProfilePage.getEmail(), personUser.getEmail(),
                "Check that email address is displayed");
        assertEquals(helpDeskUserProfilePage.getName(), personUser.getFullName(),
                "Check that the full name is displayed");
        assertEquals(helpDeskUserProfilePage.getUserName(),
                Assertion.ASSERTION_TESTER_USERNAME.assertion, "Check that username is displayed");
        assertEquals(helpDeskUserProfilePage.getLicenceNumber(),
                Assertion.ASSERTION_DRIVER_LICENCE.assertion,
                "Check that the the driver's licence is displayed for tester");
        assertEquals(helpDeskUserProfilePage.getTelephoneNumber(), personUser.getTelNo(),
                "Check that the telephone number is displayed");
        assertEquals(helpDeskUserProfilePage.getAddress(), personUser.getAddress(),
                "Check that the home address is displayed");
        assertFalse(helpDeskUserProfilePage.isPasswordResetDisplayed(),
                "Check that the password reset button is not displayed");
        assertFalse(helpDeskUserProfilePage.isUsernameResetDisplayed(),
                "Check that the username reset button is not displayed");
        qualificationStatusDisplayed(helpDeskUserProfilePage,
                Assertion.ASSERTION_QUALIFICATION_STATUS_GROUP_QUALIFIED,
                Assertion.ASSERTION_QUALIFICATION_STATUS_GROUP_QUALIFIED);
    }

    @Test(groups = {"Regression", "VM-10519", "VM-10520", "VM-10521"},
            description = "Test that validates the Super Vehicle Examiner user can asses the demo test")
    public void testDemoAssessment() {
        Person personUser = createTesterUser(TesterCreationApi.TesterStatus.DMTN);

        HelpDeskUserProfilePage helpDeskUserProfilePageDemoNeededA =
                userSearch(personUser, superVehicleExaminerUser);
        qualificationStatusDisplayed(helpDeskUserProfilePageDemoNeededA,
                Assertion.ASSERTION_QUALIFICATION_STATUS_GROUP_DEMO_TEST_NEEDED,
                Assertion.ASSERTION_QUALIFICATION_STATUS_GROUP_DEMO_TEST_NEEDED);

        RecordDemoPageGroupA recordDemoPageGroupA =
                helpDeskUserProfilePageDemoNeededA.clickRecordDemoLinkGroupA();
        QualificationChageConfirmationPage qualificationChageConfirmationPage = recordDemoPageGroupA.clickQualifiedRadioButton()
                .clickConfirm();
        HelpDeskUserProfilePage helpDeskUserProfilePage = qualificationChageConfirmationPage.clickConfirm();
        qualificationStatusDisplayed(helpDeskUserProfilePage,
                Assertion.ASSERTION_QUALIFICATION_STATUS_GROUP_QUALIFIED,
                Assertion.ASSERTION_QUALIFICATION_STATUS_GROUP_DEMO_TEST_NEEDED);
        HelpDeskUserProfilePage helpDeskUserProfilePageDemoNeededB =
                eventHistoryCheck(VehicleClassGroup.A, helpDeskUserProfilePage);

        RecordDemoPageGroupB recordDemoPageGroupB =
                helpDeskUserProfilePageDemoNeededB.clickRecordDemoLinkGroupB();

        qualificationChageConfirmationPage = recordDemoPageGroupB.clickQualifiedRadioButton().clickConfirm();
        HelpDeskUserProfilePage helpDeskUserProfilePageQualified = qualificationChageConfirmationPage.clickConfirm();
        qualificationStatusDisplayed(helpDeskUserProfilePageQualified,
                Assertion.ASSERTION_QUALIFICATION_STATUS_GROUP_QUALIFIED,
                Assertion.ASSERTION_QUALIFICATION_STATUS_GROUP_QUALIFIED);
        HelpDeskUserProfilePage newHelpDeskUserProfilePageQualified =
                eventHistoryCheck(VehicleClassGroup.B, helpDeskUserProfilePageQualified);

        newHelpDeskUserProfilePageQualified.clickLogout();

        UserDashboardPage userDashboardPage =
                UserDashboardPage.navigateHereFromLoginPage(driver, personUser.getLogin());
        userNotificationCheck(userDashboardPage, VehicleClassGroup.B);
        userNotificationCheck(userDashboardPage, VehicleClassGroup.A);
    }

    @Test(groups = {"Regression", "VM-10519", "VM-10520", "VM-10521"},
            description = "Test that validates the Vehicle Examiner user CANNOT asses the demo test")
    public void testDemoAssessmentNoPermission() {
        Person personUser = createTesterUser(TesterCreationApi.TesterStatus.DMTN);

        HelpDeskUserProfilePage helpDeskUserProfilePageDemoNeededA =
                userSearch(personUser, vehicleExaminerUser);
        qualificationStatusDisplayed(helpDeskUserProfilePageDemoNeededA,
                Assertion.ASSERTION_QUALIFICATION_STATUS_GROUP_DEMO_TEST_NEEDED,
                Assertion.ASSERTION_QUALIFICATION_STATUS_GROUP_DEMO_TEST_NEEDED);
    }

    @Test(groups = {"Regression", "VM-10519", "VM-10520", "VM-10521"},
            description = "Test that validates the Super Vehicle Examiner user can cancel the demo test")
    public void cancelDemoTestAssessment() {
        Person personUser = createTesterUser(TesterCreationApi.TesterStatus.DMTN);
        HelpDeskUserProfilePage helpDeskUserProfilePageDemoNeededA =
                userSearch(personUser, superVehicleExaminerUser);

        RecordDemoPageGroupA recordDemoPageGroupA =
                helpDeskUserProfilePageDemoNeededA.clickRecordDemoLinkGroupA();
        HelpDeskUserProfilePage helpDeskUserProfilePageDemoNeededB =
                recordDemoPageGroupA.clickCancel();

        RecordDemoPageGroupB recordDemoPageGroupB =
                helpDeskUserProfilePageDemoNeededB.clickRecordDemoLinkGroupB();
        recordDemoPageGroupB.clickCancel();
    }

    public HelpDeskUserProfilePage userSearch(Person personUser, Login DVSAUser) {
        HelpdeskUserResultsPage helpdeskUserResultsPage =
                HelpdeskUserSearchPage.navigateHereFromLoginPage(driver, DVSAUser)
                        .enterLastName(personUser.getSurname()).search();
        HelpDeskUserProfilePage helpDeskUserProfilePage = helpdeskUserResultsPage.clickUserName(0);

        return helpDeskUserProfilePage;
    }

    private void qualificationStatusDisplayed(HelpDeskUserProfilePage helpDeskUserProfilePage,
            Assertion ASSERTION_QUALIFICATION_STATUS_GROUP_A,
            Assertion ASSERTION_QUALIFICATION_STATUS_GROUP_B) {
        assertEquals(helpDeskUserProfilePage.getQualificationStatusGroupA(),
                ASSERTION_QUALIFICATION_STATUS_GROUP_A.assertion,
                "Check that the correct Qualification Status for the Group A is displayed");

        assertEquals(helpDeskUserProfilePage.getQualificationStatusGroupB(),
                ASSERTION_QUALIFICATION_STATUS_GROUP_B.assertion,
                "Check that the correct Qualification Status for the Group B is displayed");
    }

    private UserDashboardPage userNotificationCheck(UserDashboardPage userDashboardPage,
            VehicleClassGroup vehicleClassGroup) {
        NotificationPage notificationPage = userDashboardPage
                .clickNotification("Qualified : Tester Status change");

        assertEquals(notificationPage.getNotificationContent(), String.format(
                "Your tester qualification status for group %s has been changed from Demo test needed to Qualified. " +
                        "If you have any questions about this change please contact your area office.",
                vehicleClassGroup), "Checks that notification message is correct");

        return notificationPage.clickHome();
    }

    private HelpDeskUserProfilePage eventHistoryCheck(VehicleClassGroup vehicleClassGroup,
            HelpDeskUserProfilePage helpDeskUserProfilePage) {
        EventHistoryPage eventHistoryPage = helpDeskUserProfilePage.clickEventHistoryLink();

        DateTimeFormatter dateTimeFormat = DateTimeFormat.forPattern("d MMM yyyy");
        DateTime currentDate = new DateTime();
        String eventDateTime = eventHistoryPage.getEventDate();

        String expectedDate = currentDate.toString(dateTimeFormat);

        assertEquals(eventHistoryPage.getEventType(),
                String.format("Group %s Tester Qualification", vehicleClassGroup), String.format(
                        "Check to ensure Qualification Status change event type for Group %s is displayed",
                        vehicleClassGroup));

        assertEquals(eventDateTime.split(",")[0], expectedDate, String.format(
                "Check to ensure Qualification Status change event date for Group %s is displayed",
                vehicleClassGroup));

        String description = String.format(
                "Tester qualification status for group %s changed from Demo test needed",
                vehicleClassGroup);

        assertThat(String.format(
                "Check to ensure Qualification Status change event description for Group %s is displayed",
                vehicleClassGroup), eventHistoryPage.getDescription(), containsString(description));
        eventHistoryPage.clickGoBackLink();

        return new HelpDeskUserProfilePage(driver);
    }

    private Person createTesterUser(TesterCreationApi.TesterStatus status) {
        ArrayList vts = new ArrayList();
        String aeName = RandomStringUtils.randomAlphabetic(6);
        String vtsName = RandomStringUtils.randomAlphabetic(6);
        AeDetails aeDetails = aeService.createAe(aeName);

        Login aeraOfficer1User =
                new AreaOffice1UserCreationApi().createAreaOffice1User();
        int vtsId = createVTS(aeDetails.getId(), TestGroup.group1, aeraOfficer1User, vtsName);
        vts.add(vtsId);

        Person personUser = new TesterCreationApi()
                .createTesterAsPerson(Collections.singletonList(vtsId), TestGroup.group1,
                        status, aeraOfficer1User, vtsName, false, false);

        return personUser;
    }
}
