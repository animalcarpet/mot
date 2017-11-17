package uk.gov.dvsa.ui.pages.vts;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class SearchForAUserPage extends Page {
    public static final String PATH = "/vehicle-testing-station/%s/search-for-person";
    private static final String PAGE_TITLE = "Vehicle Testing Station\n" + "Assign a role";

    @FindBy(id = "search-button" ) private WebElement searchButton;
    @FindBy(id = "userSearchBox" ) private WebElement userSearchBoxInput;

    public SearchForAUserPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public SearchForAUserPage clickSearchButton() {
        searchButton.click();
        return this;
    }

    public SearchForAUserPage fillUserSearchBoxInput(String userName) {
        userSearchBoxInput.sendKeys(userName);
        return this;
    }

    public ChooseARolePage clickSearchButtonAndProgress() {
        searchButton.click();
        return new ChooseARolePage(driver);
    }
}
