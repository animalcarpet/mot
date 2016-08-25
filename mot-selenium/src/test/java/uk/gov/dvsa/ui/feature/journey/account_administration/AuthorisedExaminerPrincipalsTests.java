package uk.gov.dvsa.ui.feature.journey.account_administration;

import org.testng.annotations.Test;
import uk.gov.dvsa.ui.DslTest;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.containsString;

public class AuthorisedExaminerPrincipalsTests extends DslTest {

    @Test(groups = {"Regression", "BL-21"})
    public void createAepSuccessfully() throws IOException, URISyntaxException {
        //Given I create an AEP
        String message = motUI.organisation.
                createAuthorisedExaminerPrincipal(userData.createUserAsAreaOfficeOneUser("ao1"),
                aeData.createAeWithDefaultValues().getIdAsString());

        //Then AEP should be created Successfully
        assertThat("Aep is created successfully", message,
                containsString("has been added as a Authorised Examiner Principal."));
    }

    @Test(groups = {"Regression", "BL-21"})
    public void removeAepSuccessfully() throws IOException, URISyntaxException {
        //Given I create an AEP as an AreaOffice1User
        motUI.organisation.createAuthorisedExaminerPrincipal(userData.createUserAsAreaOfficeOneUser("ao1"),
                        aeData.createAeWithDefaultValues().getIdAsString());

        //When I remove AEP from AE
        String message = motUI.organisation.removeAepFromAe();

        //Then AEP should be removed Successfully
        assertThat("Aep is created successfully", message, containsString("has been removed successfully."));
    }
}
