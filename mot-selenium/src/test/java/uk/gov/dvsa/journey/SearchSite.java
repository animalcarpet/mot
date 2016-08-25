package uk.gov.dvsa.journey;

import uk.gov.dvsa.domain.navigation.PageNavigator;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.dvsa.SiteSearchPage;

public class SearchSite {

    private PageNavigator pageNavigator;

    public SearchSite(PageNavigator pageNavigator) {
        this.pageNavigator = pageNavigator;
    }

    public <T extends Page>T searchForSiteBySiteId(String siteId, Class<T> clazz) {
        return getSiteSearchPage().searchForSiteBySiteId(siteId).clickSearchButton(clazz);
    }

    private SiteSearchPage getSiteSearchPage() {
        return new SiteSearchPage(pageNavigator.getDriver());
    }
}
