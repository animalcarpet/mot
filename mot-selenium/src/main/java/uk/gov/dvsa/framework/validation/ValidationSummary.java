package uk.gov.dvsa.framework.validation;

import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;

import java.util.List;

public class ValidationSummary {

    public ValidationSummary() {}

    public static boolean isValidationSummaryDisplayed(MotAppDriver motAppDriver) {
        String VALIDATION_SUMMARY_SELECTOR = "//*[@id=\'validation-summary-id\']//li";
        List elements = motAppDriver.findElements(By.xpath("//*[@id=\'validation-summary-id\']//li"));
        return elements.size() > 0;
    }
}