package uk.gov.dvsa.journey;

import org.joda.time.DateTime;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.navigation.PageNavigator;
import uk.gov.dvsa.helper.enums.DateRangeFilter;
import uk.gov.dvsa.ui.pages.vts.SiteTestLogPage;

import java.io.IOException;
import java.net.URISyntaxException;

public class TestLog {

    private PageNavigator pageNavigator = null;
    private SiteTestLogPage testLogPage;

    public TestLog(PageNavigator pageNavigator) {
        this.pageNavigator = pageNavigator;
    }

    public void siteLogPage(User user, int siteId) throws IOException, URISyntaxException {
       testLogPage = pageNavigator.goToPageAsAuthorisedExaminer(user, SiteTestLogPage.class, SiteTestLogPage.PATH, siteId);
    }

    public boolean isDisplayed() {
        return testLogPage.isDataTableDisplayed();
    }

    public void home() {
        testLogPage.returnToVts();
    }

    public boolean isSelected(DateRangeFilter filter) {
        return testLogPage.isSelected(filter);
    }

    public void selectDateRange(DateTime firstDate, DateTime secondDate) {
        testLogPage.enterCustomDateRange(firstDate, secondDate);
    }

    public int getNumberOfMotTestInTable(){
        return testLogPage.getNumberOfRows();
    }
}
