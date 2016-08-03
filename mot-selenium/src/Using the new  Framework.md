#The New Test Suite
##1. Introduction
* 1.1 **The Automation Technology**   
This suite builds on the Selenium WebDriver browser automation library. It utilises the use of cookies to create valid sessions via the mot-api service, thereby removing the need to always go through the login page (which currently is a third party application) to create a session. 



* 1.2 **The Page Object Pattern  **  
The Page Object Pattern gives us a common sense way to model content in a reusable and maintainable way. it allows us to apply the same principles of modularity, reuse and encapsulation that we use in other aspects of programming to avoid redundancy within the suite.

> **Within your web app’s UI there are areas that your tests interact with. A Page Object simply models these as objects within the test code. This reduces the amount of duplicated code and means that if the UI changes, the fix need only be applied in one place.**

> -- Webdriver Wikipedia page

##2. The Mot App Web Driver
* 2.1 **The Mot App Driver Implementation**  
The entry point to new suite is the MotAppDriver object. A custom browser object marries a WebDriver instance (which drives the actual web browser being automated) with the concept of a “driver having some knowledge of the system under test”.

Example Usage in BaseTest  

	private MotAppDriver driver = null;
        
        public void setupBaseTest() {
        if (null == webDriverConfigurator.get()) {
            webDriverConfigurator.set(new WebDriverConfigurator());
        }

        driver = webDriverConfigurator.get().getDriver();

* 2.2 **The base url**  
      The driver instance maintain a baseUrl property that is used to resolve all relative URLs. This value can come from configuration or by uing the setter method.
            
* 2.3 **The User Field**  
The driver instances maintain a User property, when set, it can be used for verification purposes on the current page. The MotAppDriver has a setter method to set the User.

 		public void setUser(User user){
           this.user = user;
    	}
    
* 2.4 **The navigateToPath(String path) method**   
	This method takes a path parameter, appends it to the baseUrl and navigates to that full Url. For this to function correctly, **every page object created must have a defined public final constant PATH**, except the page cannot be navigated to directly. e.g. Modal Pages.
	
		public void navigateToPath(String path){
          remoteWebDriver.get(baseUrl + path);
    	}

* 2.5 **The loadBaseUrl() method**
This is custom method within the MotAppDriver, when set, this method simply navigate via the browser to the baseUrl without appending any path, Users can navigate the app from baseUrl via actual page interactions. 

		public void loadBaseUrl(){
           remoteWebDriver.get(baseUrl);
	    }

##3. **Page**  
The abstract class Page is a superclass for all page objects.
When using the Page Object pattern, you create subclasses of Page that initialize the content using the standard selenium webdriver PageFactory class.([Locating Elements](http://docs.seleniumhq.org/docs/02_selenium_ide.jsp#locating-elements)).  

* 3.1 **The Constructor**
The Page class is responsible for initialising the element on all child classes and performing a check that no application code has leaked into the the view. This process is handled at the constructor.
 	
 		public Page(MotAppDriver driver) {
        	this.driver = driver;
        	PageFactory.initElements(driver, this);
        	PageInteractionHelper.setDriver(driver);
        	PhpInlineErrorVerifier.verifyErrorAtPage(driver, getTitle());
   	 	}

* 3.2 **The selfVerify() method**  
This is an abstract method which must be implemented by all child class of the super class Page. Page uses this method for checking if it is pointing at a given page. it doesnt limit checking to only page title, sometimes verfification might require more than title.
 
		@Override
    	protected boolean selfVerify() {
        	PageInteractionHelper.waitForElementToBeVisible(modalTitle, Configurator.defaultWebElementTimeout);
        	return PageInteractionHelper.verifyTitle(modalTitle.getText(), PAGE_TITLE);
	    }


##4. The Navigator Class
* 4.1 **The PageNavigator Class**
The PageNavigator class handles all navigation request passed from the MotUI delegate class to other delegate classes, it removes the need for the test class to know/have deep knowledge of what page object exists. All cookie/session creation is handled within this class. It uses a generic method navigateToPage which takes a user object, string and return object.

		ProfilePage profilePage = pageNavigator.navigateToPage(user, ProfilePage.PATH, ProfilePage.class);
		
* 4.2 **The injectOpenAmCookieAndNavigateToPath(User user, String path) method.**
 This method handles setting the user in the driver instance, adding the session cookie to the current browser and calls the driver.navigateToPath() method.
 
 		private void injectOpenAmCookieAndNavigateToPath(User user, String path) throws IOException {
        	driver.setUser(user);
            addCookieToBrowser(user);
        	navigateToPath(path);
    	}

##5. Testing
* 5.1 **Build Verification Test (BVT)**  
Build Verification test is a set of tests run on every new build to verify that build is testable before it is released to test team (e.g. UAT) for further testing. These test cases are core functionality test cases that ensure application is stable and can be tested thoroughly. If BVT fails that build will be marked as failed.  

* 5.1.1 **What test should I tag as BVT?**
 The Business has a set of scenario's which are considered to be the core functionality of the application. This Jira ticket lists them ([VM-9565](https://jira.i-env.net/browse/VM-9565))
 Any feature around these functionality that can be tested as a complete journey should be tagged as BVT. If the feature cannot be tested as a complete end to end journey, it should not be tagged as BVT.
 
 e.g.
The below story covers an end to end flow of the User Account claim process.

 		Given I am on the AccountClaim page
		When I complete all forms with valid details
		Then I can get my PIN number
 		
* 5.1.2 **Writing Test**
Test clasess should be written in Gherkin Syntax

>**Given I am logged in as Wilson**  
**When I try to post to Expensive Therapy**  
**Then I should see "Your article was published.**

        //Given I have a vehicle with a failed MOT test
        motApi.createTest(tester, site.getId(), vehicle,   TestOutcome.FAILED, 12345, DateTime.now());

        //And all faults has been fixed

        //When I conduct a retest on the vehicle
        motUI.retest.conductRetestPass(vehicle, tester);

        //Then the retest is successful
        motUI.retest.verifyRetestIsSuccessful();
    }
* 5.2 **Regression Test**  
All tests created in the suite must be tagged as regression, which will enable the nightly run to pick them up and run them. Regression test mainly cover features, view logic and some error checking.

* 5.3 Creating test classes
Test classes should be created in the appropriate package within the 
	 		
	 	src/test/uk.gov.dvsa.ui
	 		
* 5.3.1 Views Package
The views package is similar to unit testing. Tests classes under this package would normally be around the views on a specific page, e.g. 

		uk.gov.dvsa.ui.views
		
		public class VehicleSearchPageViewTest extends BaseTest {

    	@Test(groups = {"Regression"}, description = "VM-9368")
    	public void breadCrumbTrailIsDisplayed() throws IOException, URISyntaxException {}
    	
* 5.3.2 Feature-> Journey Package    
This package is for tests around complete feature Journey (i.e. end to end scenarios). This a combination of pages and features that makes a complete journey. No stopping in the middle of the test to check for errors etc. 
 e.g.

		public class ContingencyMotTest extends BaseTest {
    
    	@Test(groups = {"BVT", "Regression"})
    	public void conductTestSuccessfully() throws IOException, URISyntaxException {}



##6. Running Test
* 6.1 using maven  
To run the whole the default test suite specified in the pom file.

		$ mvn clean test
		
		
	To run all test in a specific package

		$mvn clean -Dtest=uk.gov.dvsa.ui.feature.journey test

To run a single test class  
    	
    	mvn clean -Dtest=ClaimUserAccount test

* 6.2 screenshot folder

		/Users/Shared/selenium/screenshots/error
		

##7. Reporting 
* We use the ([Allure Reporting framwork](http://allure.qatools.ru/))

* Example usage ([TestNg Example](https://github.com/allure-examples/allure-testng-example))

* Wiki ([Allure Wiki](https://github.com/allure-framework/allure-core/wiki))

* There is a Jenkins plugin, which automatically compiles report, an icon will appear next to the build number, simply click and view.

* To view in Chrome, type this line into terminal                                              	
   	```open /Applications/Google\ Chrome.app --args --allow-file-access-from-files```
   	
* To run on your local and view the results in allure, run:
```mvn clean test site``` , then go to **$WORSPACE/mot-selenium/target/site/allure-maven-plugin/index.html**

* Use **uk.gov.dvsa.ui.views.EventHistoryViewTests** as reference for common allure report annotations.

##8. Best Practices
* Removed all unused import statements.
* if you added a test/pageobject class, please run entire suite to ensure no other test is broken.

