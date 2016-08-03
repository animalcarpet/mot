package uk.gov.dvsa.ui.pages.mot;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;

import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.HomePage;
import uk.gov.dvsa.ui.pages.Page;

public class MotTestStartedPage extends Page {
    private static final String PAGE_TITLE = "MOT test started";

    @FindBy(id = "sign-out") private WebElement signOut;
    @FindBy(id = "return_to_home") private WebElement returnToHome;
    @FindBy(className = "col-lg-12") private WebElement startedBanner;
    @FindBy(css = "body > div:nth-child(7) > div.row.contents > div > p:nth-child(2) > strong:nth-child(1)")
    private WebElement modelDetail;
    @FindBy(css = "body > div:nth-child(7) > div.row.contents > div > p:nth-child(2) > strong:nth-child(2)")
    private WebElement vrm;

    public MotTestStartedPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public HomePage clickReturnToHome() {
        returnToHome.click();

        return new HomePage(driver);
    }

    public String getModel() {
        return modelDetail.getText();
    }

    public String getVrm() {
        return vrm.getText();
    }
}
