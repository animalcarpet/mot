package uk.gov.dvsa.ui.feature.journey.cpms;

import org.testng.annotations.DataProvider;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.authorisedexaminer.FinanceAuthorisedExaminerViewPage;
import uk.gov.dvsa.ui.pages.cpms.*;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.is;

public class CpmsAdjustmentTests extends DslTest {

    private CardPaymentConfirmationPage financeUserPurchaseSlotsByCard(User user, String path, int aeId, int slots) throws IOException, URISyntaxException {
        CardPaymentConfirmationPage cardPaymentConfirmationPage = pageNavigator
                .goToPageAsAuthorisedExaminer(user, FinanceAuthorisedExaminerViewPage.class, path, aeId)
                .clickBuySlotsLinkAsFinanceUser()
                .selectCardPaymentTypeAndSubmit()
                .enterSlotsRequired(100)
                .clickCalculateCostButton()
                .clickContinueToPay()
                .enterCardDetails()
                .clickContinueButton()
                .enterCardHolderName()
                .clickContinueButton()
                .clickMakePaymentButtonAsFinance();
        return cardPaymentConfirmationPage;
    }

    @Test(groups = {"Regression"}, description = "SPMS-255 Finance user refunds slots", dataProvider = "createFinanceUserAndAe")
    public void userRefundsSlots(User financeUser, AeDetails aeDetails) throws IOException, URISyntaxException {

        //Given I am on Slot refund page as a Finance user with a valid payment
        SlotRefundPage slotRefundPage =
                financeUserPurchaseSlotsByCard(financeUser, FinanceAuthorisedExaminerViewPage.PATH, aeDetails.getId(), 10000)
                .clickBackToAuthorisedExaminerLink()
                .clickRefundSlotsLink();

        //When I request to refund slots providing a valid reason
        SlotRefundConfirmationPage slotRefundConfirmationPage = slotRefundPage
                .enterSlotsToBeRefunded(100)
                .selectRefundReasonAndContinue("User requested")
                .clickRefundSlotsButton();

        //Then Slots refund should be successful
        assertThat("Verifying successful refund message",
                slotRefundConfirmationPage.isRefundSuccessMessageDisplayed(), is(true));
    }

    @Test(enabled = false, groups = {"Regression"}, description = "SPMS-42 Finance User processes Payment reversal", dataProvider = "createFinanceUserAndAe")
    public void userReversesAPayment(User financeUser, AeDetails aeDetails) throws IOException, URISyntaxException {

        //Given I am on Reverse payment page of a valid payment
        ReversePaymentSummaryPage reversePaymentSummaryPage =
                financeUserPurchaseSlotsByCard(financeUser, FinanceAuthorisedExaminerViewPage.PATH, aeDetails.getId(), 10000)
                .clickViewPaymentDetailslink()
                .clickReverseThisPaymentButton();

        //When I request to reverse the payment with a valid reason
        PaymentReversalConfirmationPage paymentReversalConfirmationPage = reversePaymentSummaryPage
                .selectReasonAndConfirmPaymentReverse("Card - Chargeback request made");

        //Then Payment should be reversed successfully
        assertThat("Verifying Payment reversal successful message",
                paymentReversalConfirmationPage.isReversalSuccessfulMessageDisplayed(), is(true));

    }

    @Test(groups = {"BL-1611", "Regression"}, description = "Verify that financeuser can perform a positive manual adjustment",
          dataProvider = "createFinanceUserAndAe")
    public void userPerformsAPositiveManualAdjustment(User financeUser, AeDetails aeDetails) throws IOException, URISyntaxException {

        int numberOfSlotsBeforeAdjustment, numberOfSlotsAfterAdjustment;

        //Given I am on slot adjustment page as a finance user
        FinanceAuthorisedExaminerViewPage financeAuthorisedExaminerViewPage = pageNavigator.goToPageAsAuthorisedExaminer(financeUser,
                                                                  FinanceAuthorisedExaminerViewPage.class,
                                                                  FinanceAuthorisedExaminerViewPage.PATH,
                                                                  aeDetails.getId());
        numberOfSlotsBeforeAdjustment = financeAuthorisedExaminerViewPage.getSlotCount();

        //When I make a positive adjustment and submit changes
        financeAuthorisedExaminerViewPage.clickSlotAdjustment()
                .adjustSlots("100", "Test positive adjustment", true)
                .reviewAdjustment(ReviewSlotAdjustmentPage.class)
                .adjustSlots();

        //Then the adjustment should have taken effect
        numberOfSlotsAfterAdjustment = financeAuthorisedExaminerViewPage.getSlotCount();
        assertThat(numberOfSlotsBeforeAdjustment + 100 == numberOfSlotsAfterAdjustment, is(true));
    }

    @Test(groups = {"BL-1611", "Regression"}, description = "Verify that financeuser can perform a negative manual adjustment",
            dataProvider = "createFinanceUserAndAe")
    public void userPerformsANegativeAdjustment(User financeUser, AeDetails aeDetails) throws IOException, URISyntaxException {

        int numberOfSlotsBeforeAdjustment, numberOfSlotsAfterAdjustment;

        //Given I am on slot adjustment page as a finance user
        FinanceAuthorisedExaminerViewPage financeAuthorisedExaminerViewPage = pageNavigator.goToPageAsAuthorisedExaminer(financeUser,
                FinanceAuthorisedExaminerViewPage.class,
                FinanceAuthorisedExaminerViewPage.PATH,
                aeDetails.getId());
        numberOfSlotsBeforeAdjustment = financeAuthorisedExaminerViewPage.getSlotCount();

        //When I make a positive adjustment and submit changes
        financeAuthorisedExaminerViewPage.clickSlotAdjustment()
                .adjustSlots("100", "Test negative adjustment", false)
                .reviewAdjustment(ReviewSlotAdjustmentPage.class)
                .adjustSlots();

        //Then the adjustment should have taken effect
        numberOfSlotsAfterAdjustment = financeAuthorisedExaminerViewPage.getSlotCount();
        assertThat(numberOfSlotsBeforeAdjustment - 100 == numberOfSlotsAfterAdjustment, is(true));
    }

    @Test(groups = {"BL-1611", "Regression"}, description = "Verify that financeuser must add a comment for a manual adjustment",
            dataProvider = "createFinanceUserAndAe")
    public void userMustEnterACommentForAManualAdjustment(User financeUser, AeDetails aeDetails) throws IOException, URISyntaxException {

        //Given I am on slot adjustment page as a finance user
        FinanceAuthorisedExaminerViewPage financeAuthorisedExaminerViewPage = pageNavigator.goToPageAsAuthorisedExaminer(financeUser,
                FinanceAuthorisedExaminerViewPage.class,
                FinanceAuthorisedExaminerViewPage.PATH,
                aeDetails.getId());

        //When I make a positive adjustment without a comment
        SlotAdjustmentPage slotAdjustmentPage = financeAuthorisedExaminerViewPage.clickSlotAdjustment()
                .adjustSlots("100", "", false)
                .reviewAdjustment(SlotAdjustmentPage.class);

        //Then an error message should be displayed
        assertThat(slotAdjustmentPage.isErrorMessageDisplayed(), is(true));
    }

    @Test(groups = {"BL-1611", "Regression"},
            description = "Financeuser is able to see positive manual adjustment amount on transaction history screen",
            dataProvider = "createFinanceUserAndAe")
    public void positiveAdjustmentAmountIsShownOnPurchaseHistoryScreen(User financeUser, AeDetails aeDetails) throws IOException, URISyntaxException {

        //Given I make a positive slot count adjustment for an AE as a finance user
        FinanceAuthorisedExaminerViewPage financeAuthorisedExaminerViewPage = pageNavigator.goToPageAsAuthorisedExaminer(financeUser,
                FinanceAuthorisedExaminerViewPage.class,
                FinanceAuthorisedExaminerViewPage.PATH,
                aeDetails.getId()).clickSlotAdjustment()
                .adjustSlots("420", "Test positive adjustment", true)
                .reviewAdjustment(ReviewSlotAdjustmentPage.class)
                .adjustSlots();

        //When I go to the purchase history page for that AE
        TransactionHistoryPage transactionHistoryPage = financeAuthorisedExaminerViewPage.clickTransactionHistoryLink();

        //Then adjustment amount should be visible in the transaction history table
        assertThat("420".equals(transactionHistoryPage.getAdjustmentQuantity()), is(true));
    }

    @Test(groups = {"BL-1611", "Regression"},
            description = "Financeuser is able to see negative manual adjustment amount on transaction history screen",
            dataProvider = "createFinanceUserAndAe")
    public void negativeAdjustmentAmountIsShownOnPurchaseHistoryScreen(User financeUser, AeDetails aeDetails) throws IOException, URISyntaxException {

        //Given I make a negative slot count adjustment for an AE as a finance user
        FinanceAuthorisedExaminerViewPage financeAuthorisedExaminerViewPage = pageNavigator.goToPageAsAuthorisedExaminer(financeUser,
                FinanceAuthorisedExaminerViewPage.class,
                FinanceAuthorisedExaminerViewPage.PATH,
                aeDetails.getId()).clickSlotAdjustment()
                .adjustSlots("420", "Test negative adjustment", false)
                .reviewAdjustment(ReviewSlotAdjustmentPage.class)
                .adjustSlots();

        //And I go to the purchase history page for that AE
        TransactionHistoryPage transactionHistoryPage = financeAuthorisedExaminerViewPage.clickTransactionHistoryLink();

        //Then adjustment amount should be visible in the transaction history table
        assertThat("-420".equals(transactionHistoryPage.getAdjustmentQuantity()), is(true));
    }

    @DataProvider(name = "createFinanceUserAndAe")
    public Object[][] createFinanceUserAndAe() throws IOException {
        AeDetails aeDetails = aeData.createAeWithDefaultValues();
        User financeUser = userData.createAFinanceUser("Finance", false);
        return new Object[][]{{financeUser, aeDetails}};
    }
}
