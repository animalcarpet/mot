package uk.gov.dvsa.ui.pages.authentication.securitycard;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.interfaces.TwoFactorPromptPage;
import uk.gov.dvsa.ui.pages.Page;

public class OrderYourCardPromptPage extends Page implements TwoFactorPromptPage {
    private static final String PAGE_TITLE = "Order your security card";
    public static final String PATH= "order-card-new-user/%s";

    @FindBy(id = "order-message") private WebElement message;

    public OrderYourCardPromptPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(getTitle(), PAGE_TITLE);
    }

    @Override
    public String getText() {
        return message.getText();
    }
}
