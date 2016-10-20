package uk.gov.dvsa.ui.views;

import org.testng.Assert;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.site.ContactDetailsCountry;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.vts.ConfirmChangeDetails.ConfirmChangeDetailsAddressPage;
import uk.gov.dvsa.ui.pages.vts.VehicleTestingStationPage;

import java.io.IOException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class EditContactDetailsTests extends DslTest {

    private Site site;
    private User areaOfficeUser;
    private User siteManager;
    private String newStreet = "Test Street";
    private String newTown = "Tester Town";
    private String newPostcode = "1234 123";
    private String newEmail = "tester@dvsa.uk";
    private String newTelephone = "+768-46-6543210";

    @BeforeMethod(alwaysRun = true)
    public void setUp() throws IOException {
        site = siteData.createSite();
        areaOfficeUser = motApi.user.createUserAsAreaOfficeOneUser("dv");
        siteManager = motApi.user.createSiteManager(site.getId(), false);
    }

    @Test(groups = {"Regression"})
    public void areaOffice1UserCanChangeContactDetailsAddress() throws IOException {
        //Given I am logged in as AO1 & I navigate to the vehicle testing station page
        VehicleTestingStationPage vehicleTestingStationPage = pageNavigator.goToVtsPage(areaOfficeUser, String.valueOf(site.getId()));

        //When I navigate to change address and I change data
        ConfirmChangeDetailsAddressPage confirmTestFacilitiesPage =
                vehicleTestingStationPage.clickOnChangeAddressLink()
                        .changeFirstAddressLine(newStreet)
                        .changeTown(newTown)
                        .changePostcode(newPostcode)
                        .changeFirstAddressLine(newStreet)
                        .clickConfirmationSubmitButton();

        //Then table contains changed classes
        Assert.assertTrue(confirmTestFacilitiesPage.getAddress().contains(newStreet));
        Assert.assertTrue(confirmTestFacilitiesPage.getAddress().contains(newTown));
        Assert.assertTrue(confirmTestFacilitiesPage.getAddress().contains(newPostcode));

        //When I confirm my site classes changes
        VehicleTestingStationPage finalVehicleTestingStationPage = confirmTestFacilitiesPage.clickSubmitButton();

        //Then correct notification is displayed
        Assert.assertTrue(finalVehicleTestingStationPage.getValidationMessage().equals("Address has been successfully changed."));
    }

    @Test(groups = {"Regression"})
    public void areaOffice1UserCanChangeContactDetailsCountry() throws IOException {
        //Given I am logged in as AO1 & I navigate to the vehicle testing station pag
        VehicleTestingStationPage vehicleTestingStationPage = pageNavigator.goToVtsPage(areaOfficeUser, String.valueOf(site.getId()));

        //When I navigate to change country and I change data
        VehicleTestingStationPage finalVehicleTestingStationPage =
                vehicleTestingStationPage.clickOnChangeCountryLink()
                        .chooseOption(ContactDetailsCountry.WALES)
                        .clickSubmitButton();

        //Then my changes are displayed on the testing station page
        //And notification is displayed
        Assert.assertTrue(finalVehicleTestingStationPage.getCountryValue().equals(ContactDetailsCountry.WALES.getSiteContactDetailsCountry()));
        Assert.assertTrue(finalVehicleTestingStationPage.getValidationMessage().equals("Country has been successfully changed."));
    }

    @Test(groups = {"Regression"})
    public void areaOffice1UserCanChangeContactDetailsEmail() throws IOException {
        //Given I am logged in as AO1 & I navigate to the vehicle testing station pag
        VehicleTestingStationPage vehicleTestingStationPage = pageNavigator.goToVtsPage(areaOfficeUser, String.valueOf(site.getId()));

        //When I navigate to change email and I change data
        VehicleTestingStationPage finalVehicleTestingStationPage =
                vehicleTestingStationPage.clickOnChangeEmailLink()
                        .inputContactDetailsEmail(newEmail)
                        .clickSubmitButton();

        //Then my changes are displayed on the testing station page
        //And notification is displayed
        Assert.assertTrue(finalVehicleTestingStationPage.getEmailValue().equals(newEmail));
        Assert.assertTrue(finalVehicleTestingStationPage.getValidationMessage().equals("Email address has been successfully changed."));
    }

    @Test(groups = {"Regression"})
    public void areaOffice1UserCanChangeContactDetailsTelephone() throws IOException {
        //Given I am logged in as AO1 & I navigate to the vehicle testing station pag
        VehicleTestingStationPage vehicleTestingStationPage = pageNavigator.goToVtsPage(areaOfficeUser, String.valueOf(site.getId()));

        //When I navigate to change telephone and I change data
        VehicleTestingStationPage finalVehicleTestingStationPage =
                vehicleTestingStationPage.clickOnChangeTelephoneLink()
                        .inputContactDetailsTelephone(newTelephone)
                        .clickSubmitButton();

        //Then my changes are displayed on the testing station page
        //And notification is displayed
        Assert.assertTrue(finalVehicleTestingStationPage.getPhoneNumberValue().equals(newTelephone));
        Assert.assertTrue(finalVehicleTestingStationPage.getValidationMessage().equals("Telephone has been successfully changed."));
    }

    @Test(groups = {"Regression"})
    public void siteManagerCanSeeOnlyChangeTelephoneAndEmailLinks() throws IOException {
        //Given I am logged in as AO1 & I navigate to the vehicle testing station pag
        VehicleTestingStationPage vehicleTestingStationPage = pageNavigator.goToVtsPage(siteManager, String.valueOf(site.getId()));

        //Then I can only see change telephone and email links
        assertThat(vehicleTestingStationPage.isChangeEmailLinkDisplayed(), is(true));
        assertThat(vehicleTestingStationPage.isChangeTelephoneLinkDisplayed(), is(true));
        assertThat(vehicleTestingStationPage.isChangeAddressLinkDisplayed(), is(false));
        assertThat(vehicleTestingStationPage.isChangeCountryLinkDisplayed(), is(false));
    }
}
