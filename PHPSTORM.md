# PHPStorm integration

## Configuring PHPStorm for PHP interpreter with XDebug

Unit tests are usually run on Vagrant, however, to speed up the process (mounting through NFS slows down the runner significantly) and to run all modules with one configuration it is also possible to run unit tests with local php interpreter. 

1. Make sure you have php 5.5 installed locally at at your box. ```php -v``` to see the version. If not, you can install it with https://github.com/Homebrew/homebrew-php
2. Make sure your *XDebug* is on. Run ```php -v``` to check if it is installed. You should see the line ```with Xdebug v2.X.X, Copyright (c) 2002-2015, by Derick Rethans```. If not, you can install it with brew: ```brew install php55-xdebug```
3. Open PHPStorm, then Preferences > Languages And Frameworks > PHP
4. Set PHP Language Level to 5.5
5. Set Interpreter to point to where your PHP interpreter is installed. You can run ```which php``` to find out the path.

## Configuring PHPStorm for running unit tests

1. Download PHPUnit 4.8 from https://phar.phpunit.de
2. Open PHPStorm, then Preferences > Languages and Frameworks > PHP and add interpreter (/usr/bin/php)
3. Then Preferences > Languages and Frameworks > PHP > PHPUnit > PHPUnit Library
4. Set *Path to phpunit.phar*
5. Set *Default bootstrap file* to ```$WORKSPACE/config/phpstorm/unit-test-bootstrap-proxy/proxy.php```
6. From now on you should be able to run, debug and calculate coverage for unit tests in PHPStorm: https://www.jetbrains.com/help/phpstorm/2016.1/testing-php-applications.html?origin=old_help

## Configuring PHPStorm to run unit tests on VM ###
1. Watch this clip about remote interpreters in PHPStorm https://www.youtube.com/watch?v=YeXJP6qpu0w
2. Go to *PHPStorm prefs -> Languages & Frameworks -> PHP* -> click on 3 dots next to interpreter name to add a new *Remote* interpreter and choose Vagrant. Vagrant instances folder is ```~/MOTDEV/mot-vagrant```, PHP binary is located in ```/opt/rh/php55/root/usr/bin/php```
3. Once it is successfully added, you should select this interpreter as default for current project in *PHPStorm prefs -> Languages & Frameworks -> PHP*
4. To configure PHPUnit go to *Languages & Frameworks -> PHP -> PHPUnit* and add a new configuration *By Remote Interpreter* and select the newly added remote interpreter on Vagrant. Check *Use custom autoloader* and paste ```/home/vagrant/mot/mot-common-web-module/vendor/autoload.php``` , check *Default bootstrap file* and paste ```/home/vagrant/mot/config/phpstorm/unit-test-bootstrap-proxy/proxy.php```:
5. From now on you should be able to run, debug and calculate coverage for unit tests in PHPStorm: https://www.jetbrains.com/help/phpstorm/2016.1/testing-php-applications.html?origin=old_help


### Configuring PHPStorm to run Behat tests on VM ###
1. Add Vagrant remote interpreter, and set it as default for the project, as in the step about PHPUnit above
2. Go to *PHPStorm settings -> Languages & Frameworks -> PHP -> Behat*, add new remote configuration, and set *Path to behat dir/phar* to ```/home/vagrant/mot/mot-behat/bin/behat```
5. From now on you should be able to run and debug Behats in PHPStorm: https://www.jetbrains.com/help/phpstorm/2016.1/using-behat-framework.html?origin=old_help