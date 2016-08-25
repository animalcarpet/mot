package uk.gov.dvsa.ui.views.mot_test;

import org.testng.annotations.BeforeClass;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;
import uk.gov.dvsa.helper.ContingencyValidation;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.ui.DslTest;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.equalToIgnoringCase;
import static org.hamcrest.core.Is.is;

public class ContingencyFieldValidationTest extends DslTest {
    User tester;

    @BeforeMethod(alwaysRun = true)
    void setupUser() throws IOException, URISyntaxException {
        tester = userData.createTester(siteData.createSite().getId());
    }

    @Test(groups = {"BVT"})
    void returnValidationListWhenAllFieldsAreNull() throws IOException, URISyntaxException {
        //Given I am on the record contingency page
        motUI.contingency.testPage(tester);

        //When I submit the form without entering any values
        motUI.contingency.startTest();

        //Then I get a validation list for all empty fields
        assertThat(motUI.contingency.getValidationListSize(), is(4));
    }

    @Test(groups = {"BVT"})
    void contingencyCodeValidation() throws IOException, URISyntaxException {
        //Given I am on the record contingency page
        motUI.contingency.testPage(tester);

        //When I submit the form without entering any values
        motUI.contingency.startTest();

        //Then I get a validation list for all empty fields
        assertThat(motUI.contingency.getValidationMessage("contingency-code"),
                equalToIgnoringCase(ContingencyValidation.CT_CODE_VALIDATION_MESSAGE));
    }

    @Test(groups = {"BVT"})
    void timeIputValidation() throws IOException, URISyntaxException {
        //Given I am on the record contingency page
        motUI.contingency.testPage(tester);

        //When I submit the form without entering any values
        motUI.contingency.startTest();

        //Then I get a validation list for all empty fields
        assertThat(motUI.contingency.getValidationMessage("time"),
                equalToIgnoringCase(ContingencyValidation.TIME_VALIDATION_MESSAGE));
    }

    @Test(groups = {"BVT"})
    void dateInputValidation() throws IOException, URISyntaxException {
        //Given I am on the record contingency page
        motUI.contingency.testPage(tester);

        //When I submit the form without entering any values
        motUI.contingency.startTest();

        //Then I get a validation list for all empty fields
        assertThat(motUI.contingency.getValidationMessage("date"),
                equalToIgnoringCase(ContingencyValidation.DATE_VALIDATION_MESSAGE));
    }

    @Test(groups = {"BVT"})
    void selectReasonValidation() throws IOException, URISyntaxException {
        //Given I am on the record contingency page
        motUI.contingency.testPage(tester);

        //When I submit the form without entering any values
        motUI.contingency.startTest();

        //Then I get a validation list for all empty fields
        assertThat(motUI.contingency.getValidationMessage("reason"),
                equalToIgnoringCase(ContingencyValidation.REASON_VALIDATION_MESSAGE));
    }
}
