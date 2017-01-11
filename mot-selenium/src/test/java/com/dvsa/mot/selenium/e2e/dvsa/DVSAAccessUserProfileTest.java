package com.dvsa.mot.selenium.e2e.dvsa;

import com.dvsa.mot.selenium.datasource.Assertion;
import com.dvsa.mot.selenium.datasource.Login;
import com.dvsa.mot.selenium.datasource.Person;
import com.dvsa.mot.selenium.framework.BaseTest;
import com.dvsa.mot.selenium.priv.frontend.helpdesk.HelpDeskUserProfilePage;
import com.dvsa.mot.selenium.priv.frontend.helpdesk.HelpdeskUserResultsPage;
import com.dvsa.mot.selenium.priv.frontend.helpdesk.HelpdeskUserSearchPage;
import org.testng.annotations.Test;

import static org.testng.Assert.assertEquals;
import static org.testng.Assert.assertFalse;

public class DVSAAccessUserProfileTest extends BaseTest {


    @Test(groups = {"Regression",
            "VM-7647"}, description = "Test that validates the DVSA user roles can access user profiles")
    public void testProfileDetailsDisplayed() {

        Person user = Person.BOB_THOMAS;
        HelpdeskUserResultsPage helpdeskUserResultsPage =
                HelpdeskUserSearchPage.navigateHereFromLoginPage(driver, Login.LOGIN_AREA_OFFICE1)
                        .enterLastName(user.getSurname()).search();
        HelpDeskUserProfilePage helpDeskUserProfilePage = helpdeskUserResultsPage.clickUserName(0);

        assertEquals(helpDeskUserProfilePage.getDateOfBirth(), user.getDateOfBirth(),
                "Check that the date of birth is displayed");
        assertEquals(helpDeskUserProfilePage.getEmail(), user.getEmail(),
                "Check that email address is displayed");
        assertEquals(helpDeskUserProfilePage.getName(), user.getFullName(),
                "Check that the full name is displayed");
        assertEquals(helpDeskUserProfilePage.getUserName(),
                Assertion.ASSERTION_TESTER_USERNAME.assertion, "Check that username is displayed");
        assertEquals(helpDeskUserProfilePage.getLicenceNumber(),
                Assertion.ASSERTION_DRIVER_LICENCE.assertion,
                "Check that the the driver's licence is displayed for tester");
        assertEquals(helpDeskUserProfilePage.getTelephoneNumber(), user.getTelNo(),
                "Check that the telephone number is displayed");
        assertEquals(helpDeskUserProfilePage.getTesterAssociation(),
                Assertion.ASSERTION_TESTER_VTS.assertion,
                "check that the correct VTS is displayed for tester role");
        assertEquals(helpDeskUserProfilePage.getAddress(), user.getAddress(),
                "Check that the home address is displayed");
        assertFalse(helpDeskUserProfilePage.isPasswordResetDisplayed(),
                "Check that the password reset button is not displayed");
        assertFalse(helpDeskUserProfilePage.isUsernameResetDisplayed(),
                "Check that the username reset button is not displayed");
        assertEquals(helpDeskUserProfilePage.getQualificationStatusGroupA(),
                Assertion.ASSERTION_QUALIFICATION_STATUS_GROUP.assertion,
                "Check that the correct Qualification Status for the Group A is displayed");
        assertEquals(helpDeskUserProfilePage.getQualificationStatusGroupB(),
                Assertion.ASSERTION_QUALIFICATION_STATUS_GROUP.assertion,
                "Check that the correct Qualification Status for the Group B is displayed");

    }
}
