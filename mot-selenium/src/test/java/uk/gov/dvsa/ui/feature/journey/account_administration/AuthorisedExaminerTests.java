package uk.gov.dvsa.ui.feature.journey.account_administration;

import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.helper.ContactDetailsHelper;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.AreaOfficerAuthorisedExaminerViewPage;
import uk.gov.dvsa.ui.pages.ConfirmNewAeDetailsPage;
import uk.gov.dvsa.ui.pages.CreateAePage;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class AuthorisedExaminerTests extends DslTest {

    @Test(groups = {"FeatureToggleCreateAe"}) public void createAuthorisedExaminerSuccessfully()
            throws IOException, URISyntaxException {

        User areaOffice1User = motApi.user.createAreaOfficeOne("AreaOfficer");

        //Given I am on the Create AE screen as DVSA Admin user
        CreateAePage createAePage = pageNavigator.navigateToPage(areaOffice1User, CreateAePage.PATH, CreateAePage.class);

        //When I Create an AE using valid business details as correspondence details
        AeDetails aeDetails = new AeDetails();
        AreaOfficerAuthorisedExaminerViewPage examinerViewPage =
                createAePage.completeBusinessAndCorrespondenceDetails(aeDetails, false).create();

        //Then the new AE is created with the data provided
        assertThat(examinerViewPage.verifyNewAeCreated(aeDetails), is(true));
    }

    @Test(groups = {"FeatureToggleCreateAe"}) public void verifyConfirmAeDetailsPage()
            throws IOException, URISyntaxException {
        User areaOffice1User = motApi.user.createAreaOfficeOne("AreaOfficer");

        //Given I am on the Create AE screen as DVSA Admin user
        CreateAePage createAePage = pageNavigator.navigateToPage(areaOffice1User, CreateAePage.PATH, CreateAePage.class);

        //When the the user has entered valid data and clicks the Continue to Summary button
        AeDetails aeDetails = new AeDetails();
        ConfirmNewAeDetailsPage confirmNewAeDetailsPage =
                createAePage.completeBusinessAndCorrespondenceDetails(aeDetails, false)
                        .clickContinueToSummary();

        //Then the data entered is displayed on the confirmation screen
        confirmNewAeDetailsPage.verifyNewAeDetailsOnConfirmationPage(aeDetails, false);
    }

    @Test(groups = {"FeatureToggleCreateAe"}) public void verifyRegEmailAddressFields()
            throws IOException, URISyntaxException {
        User areaOffice1User = motApi.user.createAreaOfficeOne("AreaOfficer");

        //Given I am on the Create AE screen as DVSA Admin user
        CreateAePage createAePage = pageNavigator.navigateToPage(areaOffice1User, CreateAePage.PATH, CreateAePage.class);

        //When the user enters email address and selects 'Email address not provided' option
        createAePage.enterBusinessEmail(ContactDetailsHelper.getEmail())
                .selectBusinessEmailNotProvidedOption();

        //Then the email address fields should be empty
        assertThat(createAePage.isBusinessEmailFieldsEmpty(), is(true));
    }

    @Test(groups = {"FeatureToggleCreateAe"}) public void verifyCorrEmailAddressFieldsEmpty()
            throws IOException, URISyntaxException {
        User areaOffice1User = motApi.user.createAreaOfficeOne("AreaOfficer");

        //Given I am on the Create AE screen as DVSA Admin user
        CreateAePage createAePage = pageNavigator.navigateToPage(areaOffice1User, CreateAePage.PATH, CreateAePage.class);

        //When the user enters email address and selects 'Email address not provided' option
        AeDetails aeDetails = new AeDetails();
        createAePage.selectBusinessDetailsSameAsCorrespondenceDetails(false)
                .enterCorrespondenceAddress(aeDetails).selectCorrespondenceEmailNotProvided();

        //Then the email address fields should be empty
        assertThat(createAePage.isCorrespondenceEmailFieldsEmpty(), is(true));
    }
}
