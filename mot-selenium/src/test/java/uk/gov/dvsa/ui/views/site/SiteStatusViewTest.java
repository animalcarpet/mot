package uk.gov.dvsa.ui.views.site;

import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.site.Status;
import uk.gov.dvsa.ui.DslTest;
import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class SiteStatusViewTest extends DslTest {
    private User areaOffice2User;

    @BeforeMethod(alwaysRun = true)
    private void setup() throws IOException {
        areaOffice2User = userData.createUserAsAreaOfficeTwo("AE2");
    }

    @Test(enabled = false, groups = {"BVT"}, description = "Verifies that Authorised user can update vts status")
    public void updateVtsStatusSuccessfully() throws IOException, URISyntaxException {
        //Given I am on the VTS Details Page as Area Officer 2
        motUI.site.gotoPage(areaOffice2User, "1");

        //And I change the status of a VTS
        String newStatus = motUI.site.changeStatus(Status.EXTINCT);

        //Then the VTS status is updated
        motUI.site.vtsSearchPage(areaOffice2User);
        motUI.site.searchById("V1234");

        assertThat("The VTS status change is reflected in the results page",
                motUI.site.getStatus(),
                is(newStatus));
    }
}
