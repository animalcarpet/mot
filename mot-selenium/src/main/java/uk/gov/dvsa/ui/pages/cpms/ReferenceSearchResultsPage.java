package uk.gov.dvsa.ui.pages.cpms;

import org.openqa.selenium.By;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;

public class ReferenceSearchResultsPage extends ReferenceSearchPage {

    public ReferenceSearchResultsPage(MotAppDriver driver) {
        super(driver);
    }

    public TransactionDetailsPage chooseReference(String value) {
        driver.findElement(By.xpath(String.format("//a[contains(text(), '%s')]", value))).click();
        return new TransactionDetailsPage(driver);
    }
}
