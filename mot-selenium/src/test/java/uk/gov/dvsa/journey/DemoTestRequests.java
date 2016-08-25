package uk.gov.dvsa.journey;

import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.navigation.PageNavigator;
import uk.gov.dvsa.ui.pages.DemoTestRequestsPage;

import java.io.IOException;

public class DemoTestRequests {

    PageNavigator pageNavigator = null;
    private DemoTestRequestsPage demoTestRequestsPage;

    public DemoTestRequests(PageNavigator pageNavigator) {
        this.pageNavigator = pageNavigator;
    }

    public DemoTestRequestsPage visitDemoTestRequestsPage(User user) throws IOException {
        demoTestRequestsPage = pageNavigator.navigateToPage(user, DemoTestRequestsPage.PATH, DemoTestRequestsPage.class);
        return demoTestRequestsPage;
    }

    public boolean certificatesDisplayAmountCorrectForUser(User user, int expectedCertificateAmount) {
        return demoTestRequestsPage.usersCertificatesDisplayedAmountCorrect(user, expectedCertificateAmount);
    }
}
