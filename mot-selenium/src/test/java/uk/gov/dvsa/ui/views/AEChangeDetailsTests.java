package uk.gov.dvsa.ui.views;

import org.testng.Assert;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.*;
import uk.gov.dvsa.helper.CompanyType;
import uk.gov.dvsa.helper.ContactDetailsHelper;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.AreaOfficerAuthorisedExaminerViewPage;
import uk.gov.dvsa.ui.pages.authorisedexaminer.ConfirmChangeDetails.ConfirmChangeAECorrespondenceAddressPage;
import uk.gov.dvsa.ui.pages.authorisedexaminer.ConfirmChangeDetails.ConfirmChangeAERegisteredOfficeAddressPage;
import uk.gov.dvsa.ui.pages.authorisedexaminer.TesterAuthorisedExaminerViewPage;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class AEChangeDetailsTests extends DslTest {

    private AeDetails aeDetails;
    private User areaOfficeUser;
    private User tester;
    private String newName;
    private String newTradingName;
    private String newTown;
    private String newPostcode;
    private String newRegOfficeStreet;
    private String newRegOfficeEmail;
    private String newRegOfficeTelephone;
    private String newCorrespondenceStreet;
    private String newCorrespondenceEmail;
    private String newCorrespondenceTelephone;

    @BeforeMethod(alwaysRun = true)
    public void setUp() throws IOException {
        aeDetails = aeData.createAeWithDefaultValues();
        areaOfficeUser = motApi.user.createUserAsAreaOfficeOneUser("dv");
        Site site = siteData.createNewSite(aeDetails.getId(), "default-site");
        tester = motApi.user.createTester(site.getId());
        newName = ContactDetailsHelper.generateUniqueName();
        newTradingName = ContactDetailsHelper.generateUniqueName();
        newTown = ContactDetailsHelper.getCity();
        newPostcode = ContactDetailsHelper.getPostCode();
        newRegOfficeStreet = ContactDetailsHelper.getAddressLine1();
        newRegOfficeEmail = ContactDetailsHelper.getEmail();
        newRegOfficeTelephone = ContactDetailsHelper.getPhoneNumber();
        newCorrespondenceStreet = ContactDetailsHelper.getAddressLine1();
        newCorrespondenceEmail = ContactDetailsHelper.getEmail();
        newCorrespondenceTelephone = ContactDetailsHelper.getPhoneNumber();
    }

    @Test(groups = {"Regression"})
    public void areaOffice1UserCanChangeAEBusinessName() throws IOException, URISyntaxException {
        //Given I am logged in as AO1 & I navigate to the authorised examiner page
        AreaOfficerAuthorisedExaminerViewPage areaOfficerAuthorisedExaminerViewPage = pageNavigator
                .goToPageAsAuthorisedExaminer(areaOfficeUser, AreaOfficerAuthorisedExaminerViewPage.class, AreaOfficerAuthorisedExaminerViewPage.PATH, aeDetails.getId());

        //When I navigate to change name and I change data
        AreaOfficerAuthorisedExaminerViewPage finalAreaOfficerAuthorisedExaminerViewPage = areaOfficerAuthorisedExaminerViewPage.clickChangeNameLink()
                .inputName(newName).clickSubmitButton();

        //Then my changes are displayed on the authorised examiner
        //And notification is displayed
        Assert.assertTrue(finalAreaOfficerAuthorisedExaminerViewPage.getAeName().equals(newName));
        Assert.assertTrue(finalAreaOfficerAuthorisedExaminerViewPage.getValidationMessage().equals("Business name has been successfully changed."));
    }

    @Test(groups = {"Regression"})
    public void areaOffice1UserCanChangeAEBusinessTradingName() throws IOException, URISyntaxException {
        //Given I am logged in as AO1 & I navigate to the authorised examiner page
        AreaOfficerAuthorisedExaminerViewPage areaOfficerAuthorisedExaminerViewPage = pageNavigator
                .goToPageAsAuthorisedExaminer(areaOfficeUser, AreaOfficerAuthorisedExaminerViewPage.class, AreaOfficerAuthorisedExaminerViewPage.PATH, aeDetails.getId());

        //When I navigate to change trade name and I change data
        AreaOfficerAuthorisedExaminerViewPage finalAreaOfficerAuthorisedExaminerViewPage = areaOfficerAuthorisedExaminerViewPage.clickChangeTradingNameLink()
                .inputTradingName(newTradingName).clickSubmitButton();

        //Then my changes are displayed on the authorised examiner
        //And notification is displayed
        Assert.assertTrue(finalAreaOfficerAuthorisedExaminerViewPage.getAeTradeName().equals(newTradingName));
        Assert.assertTrue(finalAreaOfficerAuthorisedExaminerViewPage.getValidationMessage().equals("Trading name has been successfully changed."));
    }

    @Test(groups = {"Regression"})
    public void areaOffice1UserCanChangeRegisteredOfficeDetailsAddress() throws IOException, URISyntaxException {
        //Given I am logged in as AO1 & I navigate to the authorised examiner page
        AreaOfficerAuthorisedExaminerViewPage areaOfficerAuthorisedExaminerViewPage = pageNavigator
                .goToPageAsAuthorisedExaminer(areaOfficeUser, AreaOfficerAuthorisedExaminerViewPage.class, AreaOfficerAuthorisedExaminerViewPage.PATH, aeDetails.getId());

        //When I navigate to change Registered Office address and I change data
        ConfirmChangeAERegisteredOfficeAddressPage confirmAuthorisedExaminerChangeViewPage = areaOfficerAuthorisedExaminerViewPage.clickChangeRegOfficeAddressLink()
                .changeFirstAddressLine(newRegOfficeStreet)
                .changeTown(newTown)
                .changePostcode(newPostcode)
                .clickConfirmationSubmitButton();

        //Then table contains changed classes
        Assert.assertTrue(confirmAuthorisedExaminerChangeViewPage.getAddress().contains(newRegOfficeStreet));
        Assert.assertTrue(confirmAuthorisedExaminerChangeViewPage.getAddress().contains(newTown));
        Assert.assertTrue(confirmAuthorisedExaminerChangeViewPage.getAddress().contains(newPostcode));

        //When I confirm my AE registered office address changes
        AreaOfficerAuthorisedExaminerViewPage finalAreaOfficerAuthorisedExaminerViewPage = confirmAuthorisedExaminerChangeViewPage.clickSubmitButton();

        //Then correct notification is displayed
        Assert.assertTrue(finalAreaOfficerAuthorisedExaminerViewPage.getValidationMessage().equals("Registered office address has been successfully changed."));
    }

    @Test(groups = {"Regression"})
    public void areaOffice1UserCanChangeCorrespondenceDetailsAddress() throws IOException, URISyntaxException {
        //Given I am logged in as AO1 & I navigate to the authorised examiner page
        AreaOfficerAuthorisedExaminerViewPage areaOfficerAuthorisedExaminerViewPage = pageNavigator
                .goToPageAsAuthorisedExaminer(areaOfficeUser, AreaOfficerAuthorisedExaminerViewPage.class, AreaOfficerAuthorisedExaminerViewPage.PATH, aeDetails.getId());

        //When I navigate to change Correspondence address and I change data
        ConfirmChangeAECorrespondenceAddressPage confirmAuthorisedExaminerChangeViewPage = areaOfficerAuthorisedExaminerViewPage.clickChangeCorrespondenceAddressLink()
                .changeFirstAddressLine(newCorrespondenceStreet)
                .changeTown(newTown)
                .changePostcode(newPostcode)
                .clickConfirmationSubmitButton();

        //Then table contains changed classes
        Assert.assertTrue(confirmAuthorisedExaminerChangeViewPage.getAddress().contains(newCorrespondenceStreet));
        Assert.assertTrue(confirmAuthorisedExaminerChangeViewPage.getAddress().contains(newTown));
        Assert.assertTrue(confirmAuthorisedExaminerChangeViewPage.getAddress().contains(newPostcode));

        //When I confirm my AE Correspondence address changes
        AreaOfficerAuthorisedExaminerViewPage finalAreaOfficerAuthorisedExaminerViewPage = confirmAuthorisedExaminerChangeViewPage.clickSubmitButton();

        //Then correct notification is displayed
        Assert.assertTrue(finalAreaOfficerAuthorisedExaminerViewPage.getValidationMessage().equals("Correspondence address has been successfully changed."));
    }

    @Test(groups = {"Regression"})
    public void areaOffice1UserCanChangeRegisteredOfficeDetailsEmail() throws IOException, URISyntaxException {
        //Given I am logged in as AO1 & I navigate to the authorised examiner page
        AreaOfficerAuthorisedExaminerViewPage areaOfficerAuthorisedExaminerViewPage = pageNavigator
                .goToPageAsAuthorisedExaminer(areaOfficeUser, AreaOfficerAuthorisedExaminerViewPage.class, AreaOfficerAuthorisedExaminerViewPage.PATH, aeDetails.getId());

        //When I navigate to change registered office email and I change data
        AreaOfficerAuthorisedExaminerViewPage finalAreaOfficerAuthorisedExaminerViewPage = areaOfficerAuthorisedExaminerViewPage.clickChangeRegOfficeEmailLink()
                .inputContactDetailsEmail(newRegOfficeEmail)
                .clickSubmitButton();

        //Then my changes are displayed on the AE
        //And notification is displayed
        Assert.assertTrue(finalAreaOfficerAuthorisedExaminerViewPage.getAeRegEmail().equals(newRegOfficeEmail));
        Assert.assertTrue(finalAreaOfficerAuthorisedExaminerViewPage.getValidationMessage().equals("Registered office email address has been successfully changed."));
    }

    @Test(groups = {"Regression"})
    public void areaOffice1UserCanChangeCorrespondenceDetailsEmail() throws IOException, URISyntaxException {
        //Given I am logged in as AO1 & I navigate to the authorised examiner page
        AreaOfficerAuthorisedExaminerViewPage areaOfficerAuthorisedExaminerViewPage = pageNavigator
                .goToPageAsAuthorisedExaminer(areaOfficeUser, AreaOfficerAuthorisedExaminerViewPage.class, AreaOfficerAuthorisedExaminerViewPage.PATH, aeDetails.getId());

        //When I navigate to change correspondence email and I change data
        AreaOfficerAuthorisedExaminerViewPage finalAreaOfficerAuthorisedExaminerViewPage = areaOfficerAuthorisedExaminerViewPage.clickChangeCorrespondenceEmailLink()
                .inputContactDetailsEmail(newCorrespondenceEmail)
                .clickSubmitButton();

        //Then my changes are displayed on the AE
        //And notification is displayed
        Assert.assertTrue(finalAreaOfficerAuthorisedExaminerViewPage.getAeCorrEmail().equals(newCorrespondenceEmail));
        Assert.assertTrue(finalAreaOfficerAuthorisedExaminerViewPage.getValidationMessage().equals("Correspondence email address has been successfully changed."));
    }

    @Test(groups = {"Regression"})
    public void areaOffice1UserCanChangeRegisteredOfficeDetailsTelephone() throws IOException, URISyntaxException {
        //Given I am logged in as AO1 & I navigate to the authorised examiner page
        AreaOfficerAuthorisedExaminerViewPage areaOfficerAuthorisedExaminerViewPage = pageNavigator
                .goToPageAsAuthorisedExaminer(areaOfficeUser, AreaOfficerAuthorisedExaminerViewPage.class, AreaOfficerAuthorisedExaminerViewPage.PATH, aeDetails.getId());

        //When I navigate to change registered office telephone and I change data
        AreaOfficerAuthorisedExaminerViewPage finalAreaOfficerAuthorisedExaminerViewPage = areaOfficerAuthorisedExaminerViewPage.clickChangeRegOfficeTelephoneLink()
                .inputTelephone(newRegOfficeTelephone)
                .clickSubmitButton();

        //Then my changes are displayed on the AE
        //And notification is displayed
        Assert.assertTrue(finalAreaOfficerAuthorisedExaminerViewPage.getAeRegTelephoneNumber().equals(newRegOfficeTelephone));
        Assert.assertTrue(finalAreaOfficerAuthorisedExaminerViewPage.getValidationMessage().equals("Registered office telephone number has been successfully changed."));
    }

    @Test(groups = {"Regression"})
    public void areaOffice1UserCanChangeCorrespondenceDetailsTelephone() throws IOException, URISyntaxException {
        //Given I am logged in as AO1 & I navigate to the authorised examiner page
        AreaOfficerAuthorisedExaminerViewPage areaOfficerAuthorisedExaminerViewPage = pageNavigator
                .goToPageAsAuthorisedExaminer(areaOfficeUser, AreaOfficerAuthorisedExaminerViewPage.class, AreaOfficerAuthorisedExaminerViewPage.PATH, aeDetails.getId());

        //When I navigate to change correspondence telephone and I change data
        AreaOfficerAuthorisedExaminerViewPage finalAreaOfficerAuthorisedExaminerViewPage = areaOfficerAuthorisedExaminerViewPage.clickChangeCorrespondenceTelephoneLink()
                .inputTelephone(newCorrespondenceTelephone)
                .clickSubmitButton();

        //Then my changes are displayed on the AE
        //And notification is displayed
        Assert.assertTrue(finalAreaOfficerAuthorisedExaminerViewPage.getAeCorrPhone().equals(newCorrespondenceTelephone));
        Assert.assertTrue(finalAreaOfficerAuthorisedExaminerViewPage.getValidationMessage().equals("Correspondence telephone number has been successfully changed."));
    }

    @Test(groups = {"Regression"})
    public void areaOffice1UserCanChangeAeAuthStatus() throws IOException, URISyntaxException {
        //Given I am logged in as AO1 & I navigate to the authorised examiner page
        AreaOfficerAuthorisedExaminerViewPage areaOfficerAuthorisedExaminerViewPage = pageNavigator
                .goToPageAsAuthorisedExaminer(areaOfficeUser, AreaOfficerAuthorisedExaminerViewPage.class, AreaOfficerAuthorisedExaminerViewPage.PATH, aeDetails.getId());

        //When I navigate to change AE status and I change data
        AreaOfficerAuthorisedExaminerViewPage finalAreaOfficerAuthorisedExaminerViewPage =
                areaOfficerAuthorisedExaminerViewPage.clickChangeAEStatusLink()
                        .changeStatus(AEAuthStatus.SURRENDERED)
                        .clickSubmitButton();

        //Then my changes are displayed on the AE
        //And notification is displayed
        Assert.assertTrue(finalAreaOfficerAuthorisedExaminerViewPage.getAEAuthStatus().equals(AEAuthStatus.SURRENDERED.getText()));
        Assert.assertTrue(finalAreaOfficerAuthorisedExaminerViewPage.getValidationMessage().equals("Status has been successfully changed."));
    }

    @Test(groups = {"Regression"})
    public void areaOffice1UserCanChangeDVSAAreaOffice() throws IOException, URISyntaxException {
        //Given I am logged in as AO1 & I navigate to the authorised examiner page
        AreaOfficerAuthorisedExaminerViewPage areaOfficerAuthorisedExaminerViewPage = pageNavigator
                .goToPageAsAuthorisedExaminer(areaOfficeUser, AreaOfficerAuthorisedExaminerViewPage.class, AreaOfficerAuthorisedExaminerViewPage.PATH, aeDetails.getId());

        //When I navigate to change DVSA Area Office and I change data
        AreaOfficerAuthorisedExaminerViewPage finalAreaOfficerAuthorisedExaminerViewPage =
                areaOfficerAuthorisedExaminerViewPage.clickChangeDVSAAreaOfficeLink()
                        .changeAreaOffice("04")
                        .clickSubmitButton();

        //Then my changes are displayed on the AE
        //And notification is displayed
        Assert.assertTrue(finalAreaOfficerAuthorisedExaminerViewPage.getAeDVSAAreaOffice().equals("04"));
        Assert.assertTrue(finalAreaOfficerAuthorisedExaminerViewPage.getValidationMessage().equals("Area office has been successfully changed."));
    }


    @Test(groups = {"Regression"})
    public void areaOffice1UserCanChangeBusinessType() throws IOException, URISyntaxException {
        //Given I am logged in as AO1 & I navigate to the authorised examiner page
        AreaOfficerAuthorisedExaminerViewPage areaOfficerAuthorisedExaminerViewPage = pageNavigator
                .goToPageAsAuthorisedExaminer(areaOfficeUser, AreaOfficerAuthorisedExaminerViewPage.class, AreaOfficerAuthorisedExaminerViewPage.PATH, aeDetails.getId());

        //When I navigate to change business type and I change data
        AreaOfficerAuthorisedExaminerViewPage finalAreaOfficerAuthorisedExaminerViewPage =
                areaOfficerAuthorisedExaminerViewPage.clickChangeBusinessTypeLink()
                        .chooseBusinessType(CompanyType.PublicBody)
                        .clickSubmitButton();

        //Then my changes are displayed on the AE
        //And notification is displayed
        Assert.assertTrue(finalAreaOfficerAuthorisedExaminerViewPage.getBusinessTypeWithCompanyNumber().equals(CompanyType.PublicBody.getValue()));
        Assert.assertTrue(finalAreaOfficerAuthorisedExaminerViewPage.getValidationMessage().equals("Business type has been successfully changed."));
    }

    @Test(groups = {"Regression"})
    public void testerInAuthorisedExaminerPageDoesNotHaveChangeLinksOrStatusRow() throws IOException, URISyntaxException {
        //Given I am logged in as AO1 & I navigate to the authorised examiner page
        TesterAuthorisedExaminerViewPage testerAuthorisedExaminerViewPage = pageNavigator
                .goToPageAsAuthorisedExaminer(tester, TesterAuthorisedExaminerViewPage.class, TesterAuthorisedExaminerViewPage.PATH, aeDetails.getId());

        //Then There is no change links
        //And no status row
        assertThat(testerAuthorisedExaminerViewPage.areAllChangeLinksHidden(), is(true));
    }
}
