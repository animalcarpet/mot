package uk.gov.dvsa.ui.pages.nominations;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class AlreadyOrderedCardPage extends Page {
    public static final String PATH = "security-card-order/already-ordered";
    private static final String PAGE_TITLE = "Order a security card";

    @FindBy(className = "banner__heading-large") private WebElement bannerText;

    public AlreadyOrderedCardPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(getTitle(), PAGE_TITLE);
    }

    public String getBannerTitleText() {
        if(PageInteractionHelper.isElementDisplayed(bannerText)) {
            return bannerText.getText();
        }

        return "";
    }
}
