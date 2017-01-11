package com.dvsa.mot.selenium.priv.frontend.payment.pages;

import com.dvsa.mot.selenium.datasource.Address;
import com.dvsa.mot.selenium.datasource.Login;
import com.dvsa.mot.selenium.datasource.Payments;
import com.dvsa.mot.selenium.datasource.Person;
import com.dvsa.mot.selenium.framework.BasePage;
import com.dvsa.mot.selenium.priv.frontend.organisation.management.authorisedexamineroverview.pages.AuthorisedExaminerOverviewPage;

import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;

public class DirectDebitCancelConfirmationPage extends BasePage {

    private static final String PAGE_TITLE = "DIRECT DEBIT CANCELLED";

    @FindBy(id = "cancelAndReturn") private WebElement returnToAeLink;

    public DirectDebitCancelConfirmationPage(WebDriver driver) {
        super(driver);
        checkTitle(PAGE_TITLE);
    }
    
    public static DirectDebitCancelConfirmationPage loginAndCancelExistingDirectDebit(
            WebDriver driver, Login login, Payments payments, Person person, Address address) {
        return DirectDebitConfirmationPage.setupDirectDebitSuccessfully(driver, login, payments, person, address)
                .clickReturnToAeLink()
                .clickManageDirectDebitLink()
                .clickCancelDirectDebitLink()
                .clickCancelMandateButton();
    }

    public AuthorisedExaminerOverviewPage clickReturnToAeLink() {
        returnToAeLink.click();
        return new AuthorisedExaminerOverviewPage(driver);
    }

}
