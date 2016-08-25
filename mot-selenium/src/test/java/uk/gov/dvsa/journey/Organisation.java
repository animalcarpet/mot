package uk.gov.dvsa.journey;

import uk.gov.dvsa.domain.model.PersonDetails;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.navigation.PageNavigator;
import uk.gov.dvsa.ui.pages.AreaOfficerAuthorisedExaminerViewPage;
import uk.gov.dvsa.ui.pages.authorisedexaminer.Aep.CreateAepPage;
import uk.gov.dvsa.ui.pages.authorisedexaminer.Aep.ReviewCreateAepPage;

import java.io.IOException;

public class Organisation {
    private PageNavigator pageNavigator;

    public Organisation(PageNavigator pageNavigator) {
        this.pageNavigator = pageNavigator;
    }

    public String createAuthorisedExaminerPrincipal(User areaOffice1User, String aeId) throws IOException {
        CreateAepPage aepPage =
                pageNavigator.navigateToPage(areaOffice1User,
                        String.format(AreaOfficerAuthorisedExaminerViewPage.PATH, aeId),
                        AreaOfficerAuthorisedExaminerViewPage.class).clickCreateAepLink();

        aepPage.fillInForm(new PersonDetails());
        ReviewCreateAepPage reviewPage = aepPage.reviewPrincipal();
        return reviewPage.addPrincipal().getValidationMessage();
    }

    public String removeAepFromAe() {
        return new AreaOfficerAuthorisedExaminerViewPage(pageNavigator.getDriver())
                .clickRemoveAep()
                .removePrincipal()
                .getValidationMessage();
    }
}
