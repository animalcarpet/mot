package uk.gov.dvsa.framework.validation;

import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;

import java.util.List;

public class ValidationSummary {
    public ValidationSummary() {
    }

    public static boolean isValidationSummaryDisplayed(WebDriver webDriver) {
        String VALIDATION_SUMMARY_SELECTOR = "//*[@id=\'validation-summary-id\']//li";
        List elements = webDriver.findElements(By.xpath("//*[@id=\'validation-summary-id\']//li"));
        return elements.size() > 0;
    }
}