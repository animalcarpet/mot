package uk.gov.dvsa.ui.pages;

public interface Notification {

    Notification acceptNomination();
    Notification rejectNomination();
    String getConfirmationText();
    String getNotificationText();
    boolean isAcceptButtonDisplayed();
    boolean isRejectButtonDisplayed();
    void clickActivateCard();
    void clickOrderCard();
}
