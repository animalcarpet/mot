package uk.gov.dvsa.ui.pages.login;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class HardStop2faPage extends Page {
    private static final String PAGE_TITLE = "Activate your security card now";

    @FindBy(className = "banner__heading-large") private WebElement bannerText;
    @FindBy(id = "activate-card") private WebElement activateCardLink;

    public HardStop2faPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(bannerText.getText(), PAGE_TITLE);
    }
}
