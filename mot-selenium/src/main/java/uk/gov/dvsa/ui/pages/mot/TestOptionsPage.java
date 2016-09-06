package uk.gov.dvsa.ui.pages.mot;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

import java.net.URI;
import java.net.URISyntaxException;

public class TestOptionsPage extends OptionsPage {
    private static final String PAGE_TITLE = "test started";

    @FindBy(id = "sign-out") private WebElement signOut;
    @FindBy(id = "return_to_home") private WebElement returnToHome;

    public TestOptionsPage(MotAppDriver driver) {
        super(driver, PAGE_TITLE);
    }

    public void clickReturnHome(){
        clickReturnToHome();
    }


}
