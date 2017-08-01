20steps Alexa Backend
=====================

This is the backend of the "20steps" Alexa skill - the skill aims to support developers, system administrators and marketers.

Furthermore this project is a demonstration of the capabilities of the RAD platform Bricks by 20steps and used to enhance my skillset ,-)

Current capabilitites:
* Possiblity to ask UptimeRobot.com for detailed system status of your services
* Some easter egg for your loved one
* Basic Website using Pages brick to integrate Wordpress as CMS
* Basic user management including user settings
* Alexa Account linking using oAuth2 for pre-registered users

Usage:
* First register at the <a href="https://alexa.20steps.de">20steps Alexa Website</a>.
* Configure the skill e.g. by entering the API Key of your UptimeRobot account.
* After activating the skill in the Alexa App connect the previously registered account.

TODO:
* Self-registration and password reset for users at https://alexa.20steps.de  (wip)
* Publish skill at Amazon (wip)
* Some more capabilities that help developers and system administrators (any ideas welcome e.g. integration of additional services)
* Prepare Joblet for Alexa push messages
* Adapt for Echo Show (as soon as it is available in Germany)
* Localization for english language (in addition to german language)
* Enhance AbstractCustomBundle of Bricks platform to minimize glue code in custom brick as this is prob. the most simple application ever built with bricks ,-)
* Automated tests and after that invite som developers to provide speechlets ,-)

Hints for fellow coders:
* The business logic resides in the https://github.com/20steps/alexa/tree/master/src/Bricks/Custom/Twentysteps/AlexaBrick/AlexaBundle/Modules directory

## Author

* Helmut Hoffer von Ankershoffen (hhva@20steps.de)

## Sponsored by
<a href="https://20steps.de">20steps - Digital Full Service Boutique</a>

[1]:  https://github.com/20steps/bricks-installer
[2]:  https://symfony.com/
[3]:  https://api-platform.com/
[4]:  https://wordpress.org/
[5]:  http://lucene.apache.org/solr/
[6]:  https://angularjs.org/
[7]:  https://ionicframework.com/
[8]:  https://packagist.org/
[9]:  https://20steps.de

[20]:  https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/index.html
[21]:  https://symfony.com/doc/3.2/doctrine.html
[22]:  https://symfony.com/doc/3.2/templating.html
[23]:  https://symfony.com/doc/3.2/security.html
[24]:  https://symfony.com/doc/3.2/email.html
[25]:  https://symfony.com/doc/3.2/logging.html
[26]:  https://symfony.com/doc/3.2/assetic/asset_management.html
[27]:  https://symfony.com/doc/current/bundles/SensioGeneratorBundle/index.html

