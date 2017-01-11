package uk.gov.dvsa.ui.user.journeys;

import org.joda.time.DateTime;
import org.testng.annotations.DataProvider;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.*;
import uk.gov.dvsa.domain.service.ServiceLocator;
import uk.gov.dvsa.helper.TestDataHelper;
import uk.gov.dvsa.ui.BaseTest;
import uk.gov.dvsa.ui.pages.*;

import java.io.IOException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.containsString;
import static org.hamcrest.core.Is.is;
import static org.hamcrest.core.IsEqual.equalTo;

public class AedmManagesMotAccount extends BaseTest {

    @Test(groups = {"BVT"},
            description = "VM9855 - Journey 2 - AEDM Manages Mot Account", dataProvider = "createAedmAndAe")
    public void editAuthorisedExaminerDetails(User aedm, AeDetails aeDetails) throws IOException {

        //Given I am on the Authorised Examiner Change Contact Details Page
        AuthorisedExaminerChangeDetailsPage authorisedExaminerChangeDetailsPage = pageNavigator()
            .goToAuthorisedExaminerPage(aedm, AuthorisedExaminerPage.PATH, String.valueOf(aeDetails.getId()))
            .clickChangeContactDetailsLink();

        //When I Change the Authorised Examiner Correspondence Details
        AuthorisedExaminerPage authorisedExaminerPage =
            authorisedExaminerChangeDetailsPage.fillOutMinimumContactDetails(
                    aeContactDetails).saveContactDetailChanges();

        //Then the Contact Details Should be Successfully changed
        assertThat("The correspondence email has been updated correctly",
            aeContactDetails.getEmail(), is(authorisedExaminerPage.getCorrespondenceEmailText()));

        assertThat("The correspondence phone has been updated correctly",
            aeContactDetails.getTelephoneNumber(),
            is(authorisedExaminerPage.getCorrespondenceTelephoneText())
        );
    }

    @Test(groups = {"BVT"},
            description = "VM-10253 - Journey 2 - AEDM Manages Mot Account", dataProvider = "createAeAedmSiteAndTester")
    public void viewConnectedAEWithAttachedVtsAndTester(
            User aedm, AeDetails aeDetails, Site testSite, User tester) throws IOException {

        //Given I am on my Homepage as AEDM
        HomePage homePage = pageNavigator().gotoHomePage(aedm);

        //I expect to see all AE's I am connected with and their respective VTS's
        assertThat(homePage.getAeName(), equalTo(aeDetails.getAeName()));
        assertThat(homePage.getSiteName(), equalTo(testSite.getSiteNameAndNumberInHomePageFormat()));

        //When I select a VTS
        VehicleTestingStationPage vehicleTestingStationPage= homePage.selectRandomVts();

        //Then I should see all testers in that VTS
        assertThat(vehicleTestingStationPage.getTesterName(tester.getId()), equalTo(tester.getNamesAndSurname()));
    }

    @Test(groups = {"BVT"},
            description = "VM-10255 - Journey 2 - AEDM View AE Test logs", dataProvider = "createAeAedmSiteAndTester")
    public void viewAETestLogs(User aedm, AeDetails aeDetails, Site site, User tester) throws IOException {

        //Given I perform an MOT test for my selected Authorised Examiner
        ServiceLocator.getMotTestService()
                .createMotTest(tester, site.getId(),
                        TestDataHelper.getNewVehicle(), TestOutcome.PASSED, 14000, DateTime.now());

        //When I navigate to the Authorised Examiner Test Logs page
        AuthorisedExaminerTestLogPage authorisedExaminerTestLogPage =
                pageNavigator().gotoAETestLogPage(aedm, String.valueOf(aeDetails.getId()));

        //Then Today's Test count should be equal to number of test for that AE
        assertThat(authorisedExaminerTestLogPage.getTodayCount(), equalTo("1"));
    }

    @Test(groups = {"BVT"},
            description = "VM-10255 - Journey 2 - AEDM View AE Slot Usage", dataProvider = "createAeAedmSiteAndTester")
    public void viewSlotReport(User aedm, AeDetails aeDetails, Site site, User tester) throws IOException {

        //Given I perform an MOT test for my selected Authorised Examiner
        ServiceLocator.getMotTestService()
                .createMotTest(tester, site.getId(),
                        TestDataHelper.getNewVehicle(), TestOutcome.PASSED, 14000, DateTime.now());


        //When I navigate to the Slots usage Page
        AeSlotsUsagePage aeSlotsUsagePage = pageNavigator().gotoAeSlotsUsagePage(aedm, String.valueOf(aeDetails.getId()));

        //Then the Slots Usage table should contain number of test done
        assertThat(aeSlotsUsagePage.getSlotUsageCountMessage(), containsString("1 slot used today"));
    }

    @Test(groups = {"BVT"},
            description = "VM-10257 - Journey 2 - AEDM Update VTS Contact Details", dataProvider = "createAedmSite")
    public void updateVtsDetails(User aedm, Site site) throws IOException {
        String email = "blah@blah.com";
        String telephone = "+447866554432";

        //Given I am VTS Change Contact Details Page
        VtsChangeContactDetailsPage vtsChangesContactDetailsPage =
                pageNavigator().gotoVtsChangeContactDetailsPage(aedm, String.valueOf(site.getId()));

        //When I update the email and Tel Number
        vtsChangesContactDetailsPage
                .editEmailAndConfirmEmail(email, email)
                .editTelephoneNumber(telephone);
        VehicleTestingStationPage vehicleTestingStationPage = vtsChangesContactDetailsPage.clickSaveContactDetails();

        //Then I The details should updated successfully
        assertThat(vehicleTestingStationPage.getEmailValue(), equalTo("blah@blah.com"));
        assertThat(vehicleTestingStationPage.getPhoneNumberValue(), equalTo("+447866554432"));
    }

    @Test(groups = {"BVT"},
            description = "VM-10257 - Journey 2 - AEDM Remove Tester from VTS", dataProvider = "createAedmTester")
    public void removeTesterFromVTS(User aedm, User tester) throws IOException {

        //Given I am on the vehicle testing page
        HomePage homePage = pageNavigator().gotoHomePage(aedm);
        VehicleTestingStationPage vehicleTestingStationPage= homePage.selectRandomVts();

        //When I remove a tester from the VTS
        vehicleTestingStationPage.removeTesterRole(tester.getId()).confirmRemoveRole();

        //Then the tester should no longer be associated to Vts
        assertThat(vehicleTestingStationPage.isTesterDisplayed(
                tester.getId(), tester.getNamesAndSurname()), is(false)
        );
    }

    @DataProvider(name = "createAedmAndAe")
    public Object[][] createAedmAndAe() throws IOException {
        AeDetails aeDetails = TestDataHelper.createAe();
        User aedm = TestDataHelper.createAedm(aeDetails.getId(), "My_AEDM", false);

        return new Object[][]{{aedm, aeDetails}};
    }


    @DataProvider(name = "createAedmSite")
    public Object[][] createAedmSite() throws IOException {
        AeDetails aeDetails = TestDataHelper.createAe();
        User aedm = TestDataHelper.createAedm(aeDetails.getId(), "My_AEDM", false);
        Site site = TestDataHelper.createSite(aeDetails.getId(), "My_TestSite");

        return new Object[][]{{aedm, site}};
    }

    @DataProvider(name = "createAedmTester")
    public Object[][] createAedmTester() throws IOException {
        AeDetails aeDetails = TestDataHelper.createAe();
        User aedm = TestDataHelper.createAedm(aeDetails.getId(), "My_AEDM", false);
        Site site = TestDataHelper.createSite(aeDetails.getId(), "My_TestSite");
        User tester = TestDataHelper.createTester(site.getId());

        return new Object[][]{{aedm, tester}};
    }

    @DataProvider(name = "createAeAedmSiteAndTester")
    public Object[][] createAeAedmSiteAndTester() throws IOException {
        AeDetails aeDetails = TestDataHelper.createAe();
        Site testSite = TestDataHelper.createSite(aeDetails.getId(), "My_Site");
        User aedm = TestDataHelper.createAedm(aeDetails.getId(), "My_AEDM", false);
        User tester = TestDataHelper.createTester(testSite.getId());

        return new Object[][]{{aedm, aeDetails, testSite, tester}};
    }

    AeContactDetails aeContactDetails = new AeContactDetails("dummy@email.com", "dummy@email.com", "0117832934");
}
