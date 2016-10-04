package uk.gov.dvsa.ui.pages.profile;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class ReviewAddressPage extends Page {

    private static final String PAGE_TITLE = "Review address change";

    @FindBy (id = "submitAddress") private WebElement changeAddressButton;
    @FindBy (css = ".content-navigation__secondary a") private WebElement backLink;

    public ReviewAddressPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public ChangeAddressPage clickBackLink() {
        backLink.click();
        return new ChangeAddressPage(driver);
    }

    public <T extends Page> T clickChangeAddressButton(Class<T> clazz) {
        changeAddressButton.click();
        return MotPageFactory.newPage(driver, clazz);
    }
}
