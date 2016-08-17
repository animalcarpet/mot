package uk.gov.dvsa.ui.feature.journey.cpms;

import org.hamcrest.Matchers;
import org.testng.annotations.DataProvider;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.authorisedexaminer.AedmAuthorisedExaminerViewPage;
import uk.gov.dvsa.ui.pages.authorisedexaminer.FinanceAuthorisedExaminerViewPage;
import uk.gov.dvsa.ui.pages.cpms.*;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.containsString;
import static org.hamcrest.core.Is.is;

public class CpmsPurchaseSlotsTests extends DslTest {

    private AeDetails aeDetails;
    private User aedm;
    private User financeUser;

    @Test (enabled = false, groups = {"Regression"}, description = "SPMS-37 Purchase slots by card successfully", dataProvider = "createAedmAndAe")
    public void purchaseSlotsByCardSuccessfully(User aedm, AeDetails aeDetails, User financeUser) throws IOException, URISyntaxException {
        
      //Given I am on Buy test slots page as an Aedm
      BuyTestSlotsPage buyTestSlotsPage = pageNavigator
                .goToPageAsAuthorisedExaminer(aedm, AedmAuthorisedExaminerViewPage.class, AedmAuthorisedExaminerViewPage.PATH, aeDetails.getId())
                .clickBuySlotsLink();
      
      //When I submit the card payment request with required slots & valid card details
      CardPaymentConfirmationPage cardPaymentConfirmationPage = buyTestSlotsPage.enterSlotsRequired(100)
                                                      .clickCalculateCostButton().clickContinueToPay().enterCardDetails()
                                                      .clickContinueButton().enterCardHolderName().clickContinueButton()
                                                      .clickMakePaymentButton().clickCancelButton();
      
      //Then the payment should be successful
      assertThat("Verifying payment successful message is displayed",
              cardPaymentConfirmationPage.isPaymentSuccessfulMessageDisplayed(), is(true));
      assertThat("Payment status message verification",
              cardPaymentConfirmationPage.getPaymentStatusMessage(), containsString("Payment has been successful"));   
    }
    
    @Test (enabled = false, groups = {"Regression"}, description = "SPMS-264 Finance user processes Card payment", dataProvider = "createFinanceUserAndAe")
    public void financeUserProcessesCardPayment(User financeUser, AeDetails aeDetails) throws IOException, URISyntaxException {
        
      //Given I am on Choose payment type page as a Finance user
        ChoosePaymentTypePage choosePaymentTypePage = pageNavigator
                .goToPageAsAuthorisedExaminer(financeUser, FinanceAuthorisedExaminerViewPage.class, FinanceAuthorisedExaminerViewPage.PATH, aeDetails.getId())
                .clickBuySlotsLinkAsFinanceUser();
      
     //When I submit the card payment request with required slots & valid card details
      CardPaymentConfirmationPage cardPaymentConfirmationPage = choosePaymentTypePage.selectCardPaymentTypeAndSubmit()
                                                      .enterSlotsRequired(100).clickCalculateCostButton().clickContinueToPay()
                                                      .enterCardDetails().clickContinueButton().enterCardHolderName()
                                                      .clickContinueButton().clickMakePaymentButtonAsFinance();
      
      //Then the payment should be successful
      assertThat("Verifying payment successful message is displayed",
              cardPaymentConfirmationPage.isPaymentSuccessfulMessageDisplayed(), is(true));
      assertThat("Payment status message verification",
              cardPaymentConfirmationPage.getPaymentStatusMessage(), containsString("Payment has been successful"));
    }

    @Test(enabled = false, groups = {"Regression", "SPMS-77"}, dataProvider = "createAedmAndAe")
    public void financeUserSearchForPaymentByInvoiceReference(User aedm, AeDetails aeDetails, User financeUser) throws IOException, URISyntaxException {

        //Given I bought slots with card as an Aedm
        CardPaymentConfirmationPage cardPaymentConfirmationPage = purchaseSlotsAsAedmWithCard(pageNavigator
                .goToPageAsAuthorisedExaminer(aedm, AedmAuthorisedExaminerViewPage.class, AedmAuthorisedExaminerViewPage.PATH, aeDetails.getId())
                .clickBuySlotsLink());

        //And I copy invoice reference from Transaction details page
        String invoiceReference = cardPaymentConfirmationPage.clickViewPaymentDetailslink().getInvoiceNumber();

        //When I search for a invoice reference and navigate to a Transaction details page as a Finance user
        ReferenceSearchPage referenceSearchPage = pageNavigator.navigateToPage(financeUser, ReferenceSearchPage.PATH, ReferenceSearchPage.class);

        //Then expected sections should be visible
        verifyTransactionDetails(referenceSearchPage.chooseInvoiceReference().searchForReference(invoiceReference).chooseReference(invoiceReference));
    }

    @Test(enabled = false, groups = {"Regression", "SPMS-199"}, dataProvider = "createAedmAndAe")
    public void financeUserSearchForPaymentByPaymentReference(User aedm, AeDetails aeDetails, User financeUser) throws IOException, URISyntaxException {

        //Given I bought slots with card as an Aedm
        CardPaymentConfirmationPage cardPaymentConfirmationPage = purchaseSlotsAsAedmWithCard(pageNavigator
                .goToPageAsAuthorisedExaminer(aedm, AedmAuthorisedExaminerViewPage.class, AedmAuthorisedExaminerViewPage.PATH, aeDetails.getId())
                .clickBuySlotsLink());

        //And I copy payment reference from Transaction details page
        String paymentReference = cardPaymentConfirmationPage.clickViewPaymentDetailslink().getPaymentReference();

        //When I search for a payment reference and navigate to a Transaction details page as a Finance user
        ReferenceSearchPage referenceSearchPage = pageNavigator.navigateToPage(financeUser, ReferenceSearchPage.PATH, ReferenceSearchPage.class);

        //Then expected sections should be visible
        verifyTransactionDetails(referenceSearchPage.choosePaymentReference().searchForReference(paymentReference).chooseReference(paymentReference));
    }

    @Test(enabled = false, groups = {"Regression", "SPMS-47"}, dataProvider = "createAedmAndAe")
    public void paymentInvoiceDetailsVerificationTest(User aedm, AeDetails aeDetails, User financeUser) throws IOException, URISyntaxException {

        //Given I am on Buy test slots page as an Aedm
        BuyTestSlotsPage buyTestSlotsPage = pageNavigator
                .goToPageAsAuthorisedExaminer(aedm, AedmAuthorisedExaminerViewPage.class, AedmAuthorisedExaminerViewPage.PATH, aeDetails.getId())
                .clickBuySlotsLink();

        //When I click on View payment details link
        TransactionDetailsPage transactionDetailsPage = purchaseSlotsAsAedmWithCard(buyTestSlotsPage).clickViewPaymentDetailslink();

        //Then transaction details should be displayed
        assertThat("Verifying SupplierDetails displayed",
                transactionDetailsPage.getSupplierDetailsText(), Matchers.is("Supplier details"));
        assertThat("Verifying PurchaserDetails displayed",
                transactionDetailsPage.getPurchaserDetailsText(), Matchers.is("Purchaser details"));
        assertThat("Verifying PaymentDetails displayed", transactionDetailsPage.getPaymentDetailsText(),
                Matchers.is("Payment details"));
        assertThat("Verifying OrderDetails displayed", transactionDetailsPage.getOrderDetailsText(),
                Matchers.is("Order details"));
    }

    @Test(enabled = false, groups = {"Regression", "SPMS-88"}, dataProvider = "createAedmAndAe")
    public void purchaseSlotsUserCancelsPaymentTest(User aedm, AeDetails aeDetails, User financeUser) throws IOException, URISyntaxException {

        //Given I am on Buy test slots page as an Aedm
        BuyTestSlotsPage buyTestSlotsPage = pageNavigator
                .goToPageAsAuthorisedExaminer(aedm, AedmAuthorisedExaminerViewPage.class, AedmAuthorisedExaminerViewPage.PATH, aeDetails.getId())
                .clickBuySlotsLink();

        //When I click Cancel button on Card details page
        buyTestSlotsPage = buyTestSlotsPage.enterSlotsRequired(100).clickCalculateCostButton().clickContinueToPay().clickCancelButton();

        //Then I should be redirected back to Buy slots page and elements should be visible
        assertThat("Verifying RequiredSlots field present", buyTestSlotsPage.isSlotsRequiredVisible(), is(true));
        assertThat("Verifying CalculateCost button present", buyTestSlotsPage.isCalculateCostButtonVisible(), is(true));
    }

    @Test(enabled = false, groups = {"Regression", "SPMS-47"}, priority = 1)
    public void generalTransactionHistoryElementsTest() throws IOException, URISyntaxException {

        //Given I bought slots with card as an Aedm
        CardPaymentConfirmationPage cardPaymentConfirmationPage = purchaseSlotsAsAedmWithCard(pageNavigator
                .goToPageAsAuthorisedExaminer(aedm, AedmAuthorisedExaminerViewPage.class, AedmAuthorisedExaminerViewPage.PATH, aeDetails.getId())
                .clickBuySlotsLink());

        //When I'm navigating to Transaction history page
        TransactionHistoryPage transactionHistoryPage = cardPaymentConfirmationPage.clickBackToAuthorisedExaminerLink()
                .clickTransactionHistoryLink();

        //Then expected elements should be displayed
        assertThat("Verifying Number of purchases",
                transactionHistoryPage.getNumberOfTransactionsText(),
                containsString("1 purchase between"));
        assertThat("Verifying Transaction table is displayed",
                transactionHistoryPage.isTransactionsTableDisplayed(), is(true));
        assertThat("Verifying download file options displayed",
                transactionHistoryPage.isDownloadFileOptionsDisplayed(), is(true));
    }

    @Test(enabled = false, groups = {"Regression", "SPMS-47"}, priority = 2)
    public void todayTransactionHistoryVerificationTest() throws IOException, URISyntaxException {

        //Given I'm on Transaction history page as an Aedm
        TransactionHistoryPage transactionHistoryPage = pageNavigator
                .goToPageAsAuthorisedExaminer(aedm, AedmAuthorisedExaminerViewPage.class, AedmAuthorisedExaminerViewPage.PATH, aeDetails.getId()).clickTransactionHistoryLink();

        //When I'm clicking on Today link on a Transaction history page
        transactionHistoryPage.clickTodayTransactionsLink();

        //Then expected elements should be displayed
        assertThat("Verifying Number of purchases - Today",
                transactionHistoryPage.getNumberOfTransactionsText(),
                containsString("1 purchase in the today"));
        assertThat("Verifying Transaction table is displayed",
                transactionHistoryPage.isTransactionsTableDisplayed(), is(true));
        assertThat("Verifying download file options displayed",
                transactionHistoryPage.isDownloadFileOptionsDisplayed(), is(true));
    }

    @Test(enabled = false, groups = {"Regression", "SPMS-47"}, priority = 3)
    public void last7DaysTransactionHistoryVerificationTest() throws IOException, URISyntaxException {

        //Given I'm on Transaction history page as an Aedm
        TransactionHistoryPage transactionHistoryPage = pageNavigator
                .goToPageAsAuthorisedExaminer(aedm, AedmAuthorisedExaminerViewPage.class, AedmAuthorisedExaminerViewPage.PATH, aeDetails.getId()).clickTransactionHistoryLink();

        //When I'm clicking on Last 7 days link on a Transaction history page
        transactionHistoryPage.clickLast7DaysTransactionsLink();

        //Then expected elements should be displayed
        assertThat("Verifying Number of purchases - 7 days",
                transactionHistoryPage.getNumberOfTransactionsText(),
                containsString("1 purchase in the last 7 days"));
        assertThat("Verifying Transaction table is displayed",
                transactionHistoryPage.isTransactionsTableDisplayed(), is(true));
        assertThat("Verifying download file options displayed",
                transactionHistoryPage.isDownloadFileOptionsDisplayed(), is(true));
    }

    @Test(enabled = false, groups = {"Regression", "SPMS-47"}, priority = 4)
    public void last30DaysTransactionHistoryVerificationTest() throws IOException, URISyntaxException {

        //Given I'm on Transaction history page as an Aedm
        TransactionHistoryPage transactionHistoryPage = pageNavigator
                .goToPageAsAuthorisedExaminer(aedm, AedmAuthorisedExaminerViewPage.class, AedmAuthorisedExaminerViewPage.PATH, aeDetails.getId()).clickTransactionHistoryLink();

        //When I'm clicking on Last 30 days link on a Transaction history page
        transactionHistoryPage.clickLast30DaysTransactionsLink();

        //Then expected elements should be displayed
        assertThat("Verifying Number of purchases - 30 days",
                transactionHistoryPage.getNumberOfTransactionsText(),
                containsString("1 purchase in the last 30 days"));
        assertThat("Verifying Transaction table is displayed",
                transactionHistoryPage.isTransactionsTableDisplayed(), is(true));
        assertThat("Verifying download file options displayed",
                transactionHistoryPage.isDownloadFileOptionsDisplayed(), is(true));
    }

    @DataProvider(name = "createAedmAndAe")
    public Object[][] createAedmAndAe() throws IOException {
        aeDetails = aeData.createAeWithDefaultValues();
        aedm = userData.createAedm(aeDetails.getId(), "My_AEDM", false);
        financeUser = userData.createAFinanceUser("Finance", false);
        return new Object[][]{{aedm, aeDetails, financeUser}};
    }
    
    @DataProvider(name = "createFinanceUserAndAe")
    public Object[][] createFinanceUserAndAe() throws IOException {
        AeDetails aeDetails = aeData.createAeWithDefaultValues();
        User financeUser = userData.createAFinanceUser("Finance", false);
        return new Object[][]{{financeUser, aeDetails}};
    }

    private CardPaymentConfirmationPage purchaseSlotsAsAedmWithCard(BuyTestSlotsPage page) {
        return page.enterSlotsRequired(100)
                .clickCalculateCostButton().clickContinueToPay().enterCardDetails()
                .clickContinueButton().enterCardHolderName().clickContinueButton()
                .clickMakePaymentButton().clickCancelButton();
    }

    private void verifyTransactionDetails(TransactionDetailsPage page) {
        assertThat("Verifying SupplierDetails displayed", page.getSupplierDetailsText(), is("Supplier details"));
        assertThat("Verifying PurchaserDetails displayed", page.getPurchaserDetailsText(), is("Purchaser details"));
        assertThat("Verifying PaymentDetails displayed", page.getPaymentDetailsText(), is("Payment details"));
        assertThat("Verifying OrderDetails displayed", page.getOrderDetailsText(), is("Order details"));
    }
}