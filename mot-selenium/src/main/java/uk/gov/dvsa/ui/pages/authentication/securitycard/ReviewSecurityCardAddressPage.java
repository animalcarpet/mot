package uk.gov.dvsa.ui.pages.authentication.securitycard;

import org.openqa.selenium.By;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class ReviewSecurityCardAddressPage extends Page {
    private static final String PAGE_TITLE = "Review delivery address";
    public static final String PATH= "/security-card/review";

    private By orderSecurityCardButton = By.id("orderConfirmation");

    public ReviewSecurityCardAddressPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(getTitle(), PAGE_TITLE);
    }

    public ConfirmSecurityCardOrderPage orderSecurityCard(){
        driver.findElement(orderSecurityCardButton).click();
        return new ConfirmSecurityCardOrderPage(driver);
    }
}
