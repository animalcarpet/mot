package uk.gov.dvsa.journey;

import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.navigation.PageNavigator;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.dvsa.UserSearchPage;
import uk.gov.dvsa.ui.pages.dvsa.UserSearchResultsPage;

import java.util.List;

public class SearchUser {

    private PageNavigator pageNavigator;
    private static final String TOO_MANY_RESULTS_MESSAGE = "Your search for %s returned too many results.";

    public SearchUser(PageNavigator pageNavigator) {
        this.pageNavigator = pageNavigator;
    }

    public <T extends Page>T searchForUserByUserEmail(String email, boolean isSectionExpanded, Class<T> clazz) {
        if (isSectionExpanded) {
            return fillEmailAsSearchCriteria(email).clickSearchButton(clazz);
        }
        return fillEmailAsSearchCriteria(email).clickAdditionalSearchCriteriaLink()
                .clickSearchButton(clazz);
    }

    public UserSearchResultsPage searchForUserByTown(String town) {
        return getUserSearchPage().clickAdditionalSearchCriteriaLink()
                .searchForUserByTown(town)
                .clickSearchButton(UserSearchResultsPage.class);
    }

    public <T extends Page>T searchForUserByDateOfBirth(String date, boolean isDateValid) {
        if (isDateValid) {
            return (T) fillDateOfBirthAsSearchCriteria(date).clickSearchButton(UserSearchResultsPage.class);
        }
        return (T) fillDateOfBirthAsSearchCriteria(date).clickSearchButton(UserSearchPage.class);
    }

    public <T extends Page>T searchForUserByUsername(String username, Class<T> clazz) {
        return getUserSearchPage().searchForUserByUsername(username).clickSearchButton(clazz);
    }

    public <T extends Page>T searchForUserByUserFirstName(String userName, Class<T> clazz) {
        return getUserSearchPage().searchForUserByFirstName(userName).clickSearchButton(clazz);
    }

    public boolean isUserSearchResultAccurate(User user) {
        UserSearchResultsPage userSearchResultsPage = new UserSearchResultsPage(pageNavigator.getDriver());
        List<String> userDetails = userSearchResultsPage.getUserDetails();
        boolean userName = userDetails.get(0).equals(user.getNamesAndSurname());
        boolean address = userDetails.get(1).contains(user.getAddressLine1());
        boolean postcode = userDetails.get(2).equals(user.getPostcode());
        return userName && address && postcode;
    }

    public boolean isSearchResultAccurateWhenSearchingByTown(String town) {
        return getUserSearchResultsPage().getUserDetails().get(1).contains(town);
    }

    public boolean isSearchResultAccurateWhenSearchingByDOB(String date) {
        String[] inputDate = date.split("-");
        String outputDate = getUserSearchResultsPage().getDateOfBirth();
        return outputDate.contains(inputDate[0]) && outputDate.contains(inputDate[2]);
    }

    public boolean isErrorMessageDisplayed() {
        return getUserSearchPage().isErrorMessageDisplayed();
    }

    public boolean isNoResultsMessageDisplayed() {
        return getUserSearchPage().isNoResultsMessageDisplayed();
    }

    public boolean isTooManyResultsMessageDisplayed(String email) {
        return getUserSearchPage().getValidationMessageText().contains(String.format(TOO_MANY_RESULTS_MESSAGE, email));
    }

    public boolean isSearchButtonDisplayed() {
        return getUserSearchPage().isSearchButtonDisplayed();
    }

    private UserSearchPage fillEmailAsSearchCriteria(String email) {
        return getUserSearchPage().clickAdditionalSearchCriteriaLink()
                .searchForUserByUserEmail(email);
    }

    private UserSearchPage fillDateOfBirthAsSearchCriteria(String date) {
        return getUserSearchPage().clickAdditionalSearchCriteriaLink()
                .searchForUserByDateOfBirth(date);
    }

    private UserSearchPage getUserSearchPage() {
        return new UserSearchPage(pageNavigator.getDriver());
    }

    private UserSearchResultsPage getUserSearchResultsPage() {
        return new UserSearchResultsPage(pageNavigator.getDriver());
    }
}
