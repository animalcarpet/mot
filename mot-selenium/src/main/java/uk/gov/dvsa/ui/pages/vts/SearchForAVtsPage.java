package uk.gov.dvsa.ui.pages.vts;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class SearchForAVtsPage extends Page {

    public static final String PATH = "/vehicle-testing-station/search";
    private static final String PAGE_TITLE = "Search for site information by...";

    @FindBy(id = "site_number") private WebElement siteIdInputBox;
    @FindBy(id = "site_name") private WebElement siteNameInputBox;
    @FindBy(id = "site_postcode") private WebElement sitePostcodeInputBox;
    @FindBy(id = "submitSiteSearch") private WebElement searchButton;

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public SearchForAVtsPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    public <T extends Page>T searchForVts(Class<T> clazz, String siteId) {
        FormDataHelper.enterText(siteIdInputBox, siteId);
        searchButton.click();
        return MotPageFactory.newPage(driver, clazz);
    }
}
