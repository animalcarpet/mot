package uk.gov.dvsa.ui.pages.mot;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.HomePage;
import uk.gov.dvsa.ui.pages.Page;

public class SurveyThankYouPage extends Page {

    @FindBy(id = "back-to-home-link") private WebElement backToHomeLink;

    private static final String PAGE_TITLE = "Thank you for your feedback";

    public SurveyThankYouPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public HomePage clickBackHomeLink() {
        backToHomeLink.click();
        return MotPageFactory.newPage(driver, HomePage.class);
    }
}
