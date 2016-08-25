package uk.gov.dvsa.journey.userprofile;

import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.exception.PageInstanceNotFoundException;
import uk.gov.dvsa.ui.pages.profile.*;

public class ChangeAddress {

    private ProfilePage profilePage;
    private ChangeAddressPage changeAddressPage;
    private ReviewAddressPage reviewAddressPage;

    private static final String ADDRESS_MUST_BE_CORRECT_MESSAGE = "you must enter the first line of the address";
    private static final String TOWN_MUST_BE_CORRECT_MESSAGE = "you must enter a town or city";
    private static final String POSTCODE_MUST_BE_CORRECT_MESSAGE = "must be a valid postcode";

    public ChangeAddress(ProfilePage profilePage) {
        this.profilePage = profilePage;
    }

    public <T extends Page> T changeAddress(String firstLine, String town, String postcode, String value) {
        changeAddressPage = profilePage.clickChangeAddressLink().fillFirstLine(firstLine).fillTown(town).fillPostcode(postcode);

        switch (value) {
            case "INVALID_INPUT":
                return (T) changeAddressPage.clickReviewAddress(ChangeAddressPage.class);
            case "PERSON_PROFILE":
                return (T) changeAddressPage.clickReviewAddress(ReviewAddressPage.class).clickChangeAddressButton(NewPersonProfilePage.class);
            case "USER_PROFILE":
                return (T) changeAddressPage.clickReviewAddress(ReviewAddressPage.class).clickChangeAddressButton(NewUserProfilePage.class);
            default:
                throw new PageInstanceNotFoundException("Page instantiation exception");
        }
    }

    public boolean isValidationMessageOnChangeAddressPageDisplayed(String warningMessage) {
        switch (warningMessage) {
            case "FIRST_LINE_INVALID":
                return changeAddressPage.getFieldValidationMessage().equals(ADDRESS_MUST_BE_CORRECT_MESSAGE)
                        && changeAddressPage.getPageValidationMessage().contains(ADDRESS_MUST_BE_CORRECT_MESSAGE);
            case "TOWN_INVALID":
                return changeAddressPage.getFieldValidationMessage().equals(TOWN_MUST_BE_CORRECT_MESSAGE)
                        && changeAddressPage.getPageValidationMessage().contains(TOWN_MUST_BE_CORRECT_MESSAGE);
            case "POSTCODE_INVALID":
                return changeAddressPage.getFieldValidationMessage().equals(POSTCODE_MUST_BE_CORRECT_MESSAGE)
                        && changeAddressPage.getPageValidationMessage().contains(POSTCODE_MUST_BE_CORRECT_MESSAGE);
            case "INPUT_INVALID":
                return changeAddressPage.getPageValidationMessage().contains(ADDRESS_MUST_BE_CORRECT_MESSAGE)
                        && changeAddressPage.getPageValidationMessage().contains(TOWN_MUST_BE_CORRECT_MESSAGE)
                        && changeAddressPage.getPageValidationMessage().contains(POSTCODE_MUST_BE_CORRECT_MESSAGE);
            default:
                return false;
        }
    }

    public ChangeAddress navigateToReviewAddress() {
        this.reviewAddressPage = profilePage.clickChangeAddressLink().clickReviewAddress(ReviewAddressPage.class);
        return this;
    }

    public ChangeAddress navigateFromReviewAddressToChangeAddress() {
        if (reviewAddressPage == null) {
            navigateToReviewAddress();
            reviewAddressPage.clickBackLink();
        }
        this.changeAddressPage = reviewAddressPage.clickBackLink();
        return this;
    }

    public <T extends ProfilePage> T navigateFromChangeAddressToPersonProfile(boolean isPersonProfile) {
        if (changeAddressPage == null) {
            profilePage.clickChangeAddressLink();
        }
        if (isPersonProfile) {
            return (T)changeAddressPage.clickCancelAndReturn(NewPersonProfilePage.class);
        }
        return (T)changeAddressPage.clickCancelAndReturn(NewUserProfilePage.class);
    }
}
