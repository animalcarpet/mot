package uk.gov.dvsa.ui.pages.mot.modal;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import uk.gov.dvsa.framework.config.Configurator;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class ManualReferenceModalPage extends Page{

    private String referenceId;
    private WebElement modalTitle;
    private WebElement externalRfrLink;

    private static final String PAGE_TITLE = "Manual reference";

    public ManualReferenceModalPage(MotAppDriver driver, String referenceId) {
        super(driver);
        this.referenceId = referenceId;
        initElements();
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        PageInteractionHelper.waitForElementToBeVisible(modalTitle, Configurator.defaultWebElementTimeout);
        return PageInteractionHelper.verifyTitle(modalTitle.getText(), PAGE_TITLE);
    }

    public void clickOnExternalRfrLink() {
        externalRfrLink.click();
    }

    private void initElements() {
        modalTitle = driver.findElement(By.id(prepareLocatorWithRfrId("rfr-details-dialog-%s"))).findElement(By.tagName("h3"));
        externalRfrLink = driver.findElement(By.id(prepareLocatorWithRfrId("manual-btn-%s")));
    }

    private String prepareLocatorWithRfrId(String id) {
        return String.format(id, referenceId);
    }
}
