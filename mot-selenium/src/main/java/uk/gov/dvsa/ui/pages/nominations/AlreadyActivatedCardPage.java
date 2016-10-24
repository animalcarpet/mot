package uk.gov.dvsa.ui.pages.nominations;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class AlreadyActivatedCardPage extends Page {
    public static final String PATH = "register-card/already-has-card";
    private static final String PAGE_TITLE = "Activate your security card";

    public AlreadyActivatedCardPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @FindBy(className = "banner--error") private WebElement bannerText;

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(getTitle(), PAGE_TITLE);
    }

    public String getBannerTitleText() {
        if(PageInteractionHelper.isElementDisplayed(bannerText)) {
            return bannerText.getText();
        }

        return "No text to display";
    }
}
