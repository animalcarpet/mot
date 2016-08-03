package uk.gov.dvsa.ui.pages.dvsa;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class SiteSearchPage extends Page{

    private static final String PAGE_TITLE = "Site search";
    public static final String PATH = "/vehicle-testing-station/search";

    @FindBy(id = "site_number") private WebElement siteIdInputField;
    @FindBy(id = "submitSiteSearch") private WebElement searchButton;

    public SiteSearchPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(getTitle(), PAGE_TITLE);
    }

    public SiteSearchPage searchForSiteBySiteId(String siteId) {
        FormDataHelper.enterText(siteIdInputField, String.valueOf(siteId));
        return this;
    }

    public <T extends Page> T clickSearchButton(Class<T> clazz) {
        searchButton.click();
        return MotPageFactory.newPage(driver, clazz);
    }
}
