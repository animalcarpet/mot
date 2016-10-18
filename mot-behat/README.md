API Testing
===========

This project provides a mechanism to test various API endpoints within
the MOT application.

Project setup
-------------

[Install composer](https://getcomposer.org/download/) if you haven't got
it installed yet. Next, use composer to install project's dependencies:

    composer install
    
*Alternatively, follow PHPStorm configuration steps in order to install
dependencies with the IDE.*

Project uses [php-cs-fixer](http://cs.sensiolabs.org) to maintain
consistent coding standards. Before submitting a MR make sure your code
complies with the standards:

    ./vendor/bin/php-cs-fixer fix

PHPStorm and Behat on Mac
-------------------------

Latest OS X comes with PHP 5.5 which should be enough to run this project.
[PHPStorm](https://www.jetbrains.com/phpstorm/) is the recommended IDE.

### Installing a non-default PHP version (optional)

Install [brew](http://brew.sh).

Setup the `homebrew/dupes`, `homebrew/versions` and `homebrew/homebrew-php`
taps which has dependencies we need:

    brew tap homebrew/dupes
    brew tap homebrew/versions
    brew tap homebrew/homebrew-php

Once the tap is installed, you can install php53, php54, php55, php56,
or any formulae you might need via:

    brew install php56

### PHPStorm Configuration

Configure Composer (optional):
* Open PHPStorm Menu:
  *File* -> *Languages & Frameworks* -> *Default Settings* -> *PHP* -> *Composer*
* Enter Path to PHP Executable `/usr/bin/php`
  (or `/usr/local/bin/php` if you used brew to install PHP)
* Install project dependencies with Composer


Run Behat (optional):
* Open PHPStorm Menu:
  *File* -> *Default Settings* -> *Languages & Frameworks* -> *PHP* -> *Behat*
* In Path to Behat Directory enter the location of Behat installed in your project:
  /Users/<username>/DVSA/api-testing-behat/vendor/behat/behat/bin/behat
* Make sure you click the refresh button
* Feature file functions should now be available, to confirm right click on a
  scenario and choose the Run 'feature name: scenario name'

Running tests
-------------

* Navigate to the project directory, e.g.

    cd $HOME/DVSA/api-testing-behat

* Run Behat on the local machine

    vendor/bin/behat
 
 
Implementing our first feature
------------------------------

**Before you continue**, read all the
[Behat docs](http://docs.behat.org/en/latest/)
to learn more about the tool
(specifically the quick intro and the guides).
Be careful to read the latest 3.x docs, and not 2.x.

### The feature file

Create a new feature file in the `features/` folder,
for example `features/tester_performs_an_mot_test.feature`:

    Feature: Tester performs an MOT Test
      As a Tester
      I want to Perform an MOT Test
      So that I can verify a Vehicle is Road Worthy
    
      Scenario: Complete an MOT Test with No Reading
        Given I started an MOT Test as an Authenticated Tester
        And I added an Odometer Reading "NOT READ"
        And I added a Class 3-7 Roller Brake Test Result
        When I Pass the MOT Test
        Then the MOT Test Status should be "PASSED"

### The context class

Create a new Context class (right click on
`features/StepDefinitions` -> `New` -> `PHP Class`),
for example `features/StepDefinitions/TesterPerformsMotTestContext.php`.

As a rule of thumb, main steps of each feature file should go to a
dedicated context class. However, there are some generic steps (like
authentication),
which will be reused between features and will often go to its own context.

Implement the `Behat\Behat\Context\Context` interface:

    <?php
    
    use Behat\Behat\Context\Context;
    use Behat\Behat\Context\SnippetAcceptingContext;
    
    class TesterPerformsMotTestContext implements Context, SnippetAcceptingContext
    {
    }

**Hint**: Additionally implementing the `Behat\Behat\Context\SnippetAcceptingContext`
will help Behat to decide where to add new steps. We'll find ourselves changing
the class that implements this interface depending on the feature we're working on.

You need to register the new context class in suites we want to use it with:

    default:
        # ...
        suites:
            default:
                contexts:
                    - FeatureContext
                    # ...
                    - TesterPerformsMotTestContext

### Steps

If we run behat now (i.e. `./vendor/bin/behat features/tester_performs_an_mot_test.feature`),
it will output a bunch of undefined steps. It simply doesn't know what code
should be executed.

You can either copy those steps and paste them to the context class, or run behat with
the `--append-snippets` switch to let it update context class automatically:

    ./vendor/bin/behat features/tester_performs_an_mot_test.feature --append-snippets

For the automatic update to work, one of our context classes needs to implement the
`Behat\Behat\Context\SnippetAcceptingContext`.

### Step implementation

If we run Behat now:

    ./vendor/bin/behat features/tester_performs_an_mot_test.feature

we'll see that it notifies us of pending steps.

Any step throwing a `PendingException` is treated by Behat as "not implemented yet".
It's neither a failure nor a success. Throwing any other exception will make
the step failed. Behat assumes a step passed if there was no exception thrown.

Context classes should be treated as controllers are in web development.
Most of the time they'll be proxying calls to other objects,
and verifying results.

In this project we'll be mainly working with the API, so our context class
methods will be calling an API client. There's also few endpoints
implemented in this project. You'll find them in `src/Support/Api` folder.

In case of our first step - "Given I started an MOT Test as an Authenticated Tester",
we'll need to actually perform two API calls:

* authenticate as a Tester
* start an MOT test

The best way is to think of a perfect API for our calls first,
and implement them as a second step. This will guarantee simplicity of
context classes.

Implementing the first step will be failrly simple,
since can re-use two existing Api classes - Session and MotTest:

    <?php
    
    use Behat\Behat\Tester\Exception\PendingException;
    use Behat\Behat\Context\Context;
    use Dvsa\Mot\Behat\Datasource\Authentication;
    use Dvsa\Mot\Behat\Support\Api\MotTest;
    use Dvsa\Mot\Behat\Support\Api\Session;
    
    class TesterPerformsMotTestContext implements Context
    {
        /**
         * @var Session
         */
        private $session;
        
        /**
         * @var MotTest
         */
        private $motTest;
        
        private $startedMotTest;
    
        public function __construct(Session $session, MotTest $motTest)
        {
            $this->session = $session;
            $this->motTest = $motTest;
        }
        
        /**
         * @Given I started an MOT Test as an Authenticated Tester
         */
        public function iStartedAnMotTestAsAnAuthenticatedTester()
        {
            $user = $this->session->startSession(Authentication::$loginTester1, Authentication::$passwordDefault);
            $this->startedMotTest = $this->motTest->startMOTTestForTester($user->getAccessToken(), $user->getUserId());
        }
        
        // ...
    }
    
The Api classes can be automatically injected into our context classes
without having to worry on how they're created. All we have to do
is to put it on the list of constructor arguments, including
a type hint.

Added benefit of injecting services is that they can be shared
between contexts. If our steps are defined in multiple contexts,
the injected services will only be created once.

### Api endpoints

Classes defined in `src/Support/Api` represent API endpoints.
Methods on these classes abstract the API calls.

The simplest way to create a new API client is to extend the
`Dvsa\Mot\Behat\Support\Api\Api` base class, which gives
us access to an http client (`$this->client` property):

    <?php
    
    namespace Dvsa\Mot\Behat\Support\Api;
    
    use Dvsa\Mot\Behat\Datasource\Authentication;
    use Dvsa\Mot\Behat\Support\Response;
    use Dvsa\Mot\Behat\Support\Request;
    
    class MotTest extends Api
    {
        const PATH = 'mot-test';
    
        /**
         * @param string $token
         * @param string $userId
         *
         * @return Response
         */
        public function startMOTTestForTester($token, $userId)
        {
            $body = json_encode([
                'vehicleId' => '3',
                'vehicleTestingStationId' => '1',
                'primaryColour' => 'L',
                'secondaryColour' => 'L',
                'fuelTypeCode' => 'PE',
                'vehicleClassCode' => '4',
                'hasRegistration' => '1',
                'oneTimePassword' => Authentication::$oneTimePassword
            ]);
    
            return $this->client->request(new Request(
                'POST',
                self::PATH,
                ['Content-Type' => 'application/json', 'Authorization' => 'Bearer '.$token],
                $body
            ));
        }
    }

### Injecting services into a context

In order to let Behat inject services automatically into
our contexts we need to register them in Behat's DI container.

**Note:** Behat uses
[Symfony's DIC component](http://symfony.com/doc/current/components/dependency_injection/introduction.html#setting-up-the-container-with-configuration-files). 
It'll be easier to understand how service injection works
if you understand how the component works.

For example, to register a new API endpoint as a service,
we could to add the following entry to the `config/api.yml`:

    dvsa.my_mot_test:
        class: Dvsa\Mot\Behat\Support\Api\MyMotTest
        parent: dvsa.api

In this case we used the `dvsa.api` service definition
as a parent service. It works as a template, and our service will
inherit all configurations from the parent (among others - the arguments).

**Note:** Service definitions are imported with the
[NoExtension](https://github.com/jakzal/BehatNoExtension).

Currently, a new service needs to be also added to a list
of supported services in the `config/arguments.yml`:

    parameters:
        dvsa.argument_resolver.services:
              Dvsa\Mot\Behat\Support\Api\MyMotTest: dvsa.my_mot_test
              
It is a simple map of a namespace (needs to match the type hint
in our context class) to a service id (the id we gave when registering
the service).
