package uk.gov.dvsa.helper;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.ui.Select;

public class FormDataHelper {

    public static void selectFromDropDownByValue(WebElement element, String value){
        Select dropdown = new Select(element);
        dropdown.selectByValue(value);
    }

    public static void selectFromDropDownByVisibleText(WebElement element, String value){
        Select dropdown = new Select(element);
        dropdown.selectByVisibleText(value);
    }

    public static void selectInputBox(WebElement webElement) {
        if(webElement.isDisplayed() && !webElement.isSelected()) {
            webElement.click();
        }
    }

    public static void enterText(WebElement webElement, String value) {
        webElement.click();
        webElement.clear();
        webElement.sendKeys(value);
    }

    public static String getSelectedTextFromDropdown(WebElement elementLocator) {
        return new Select(elementLocator).getFirstSelectedOption().getText();
    }

    public static void enterInputRadioButtonOrCheckbox (WebElement webElement, boolean checked) {
        if (checked != webElement.isSelected())
            webElement.click();
    }
}
