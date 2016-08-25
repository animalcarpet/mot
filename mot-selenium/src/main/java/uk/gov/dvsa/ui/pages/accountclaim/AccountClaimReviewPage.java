package uk.gov.dvsa.ui.pages.accountclaim;


import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;

import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class AccountClaimReviewPage extends Page {

    private static final String PAGE_TITLE = "Review account details";

    @FindBy(id = "btSubmitForm") private WebElement claimYourAccountButton;

    @FindBy(id = "go-to-previous-page") private WebElement goBackLink;

    public AccountClaimReviewPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public AccountClaimConfirmationPage clickBackButton() {
        goBackLink.click();
        return new AccountClaimConfirmationPage(driver);
    }

    public <T extends Page> T clickClaimYourAccountButton(Class<T> expectedResultPageClass) {
        claimYourAccountButton.click();
        return MotPageFactory.newPage(driver, expectedResultPageClass);
    }
}
