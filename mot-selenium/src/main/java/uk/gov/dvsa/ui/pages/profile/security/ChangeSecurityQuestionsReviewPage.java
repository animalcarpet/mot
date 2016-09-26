package uk.gov.dvsa.ui.pages.profile.security;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class ChangeSecurityQuestionsReviewPage extends Page {
    private static final String PAGE_TITLE = "Review security question changes";

    @FindBy(id = "orderConfirmation") private WebElement saveChangesButton;

    public ChangeSecurityQuestionsReviewPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(getTitle(), PAGE_TITLE);
    }

    public ChangeSecurityQuestionsSuccessPage saveChanges() {
        saveChangesButton.click();
        return new ChangeSecurityQuestionsSuccessPage(driver);
    }

}
