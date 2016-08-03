package uk.gov.dvsa.ui.pages.mot;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class MotTestSearchPage extends Page {

    public static final String PATH = "/mot-test-search";
    private static final String PAGE_TITLE = "Search for MOT tests by...";

    @FindBy(id = "type") private WebElement searchTypePrompt;

    @FindBy(id = "vts-search") private WebElement searchInput;

    @FindBy(id = "item-selector-btn-search") private WebElement searchButton;

    public MotTestSearchPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public MotTestSearchPage selectSearchCategory(String searchCategory) {
        FormDataHelper.selectFromDropDownByValue(searchTypePrompt, searchCategory);

        return this;
    }

    public MotTestSearchPage fillSearchValue(String searchValue) {
        searchInput.sendKeys(searchValue);

        return this;
    }

    public <T extends Page> T clickSearchButton(Class<T> clazz) {
        searchButton.click();
        return MotPageFactory.newPage(driver, clazz);
    }

    public void clickSearchButton() {
        searchButton.click();
    }
}
