20steps Alexa Backend
=====================

This is the certified backend of the personalized and localized "20steps" Alexa skill - the skill aims to support developers, system administrators and marketers

Publishing for Google Home is work in progress (cp. below).

Furthermore this project is a demonstration of the capabilities of the RAD platform Bricks by 20steps, availability and performance characterics of the Bricks Cloud and last but not least used to enhance my skillset during holidays.

Current capabilitites:
* Possiblity to ask UptimeRobot.com for detailed system status of your web services
* Some easter egg for your loved one
* Support for german and english language
* User management including self-registration, double-opt-in, user settings, password reset, resend activation link etc.
* Support for Amazon Alexa Webhook API
* Support for Alexa Account linking via oAuth2
* Certified with Amazon (cp. <a target="_blank" href="https://www.amazon.de/20steps-Digital-Full-Service-Boutique/dp/B074HHVYQ7">Skill in Amazon Store</a>).
* Support for Sign in with Google
* Basic Support for Google Assistant / Actions / API AI Webhook API
* Basic Support for Google Assistant / Account linking
* Basic support for Google Assistant / Account linking / Streamlined Identity Flow (cp. https://developers.google.com/actions/identity/oauth2-assertion-flow)

Technical specs:
* Built using the RAD Bricks platform in 4 days, start to publish, without any prior knowledge of Alexa
* Responsive and localized yet minimal Website using Pages brick to integrate Wordpress as CMS and Twig/Bootstrap for layouting
* High performance and availability deployment at Bricks Cluster including HTTP/2, SSL offloader, CDN via keycdn, CentOS containers, Varnish layer, GlusterFS, HHVM+PHP7, <a target="_blank" href="https://developers.google.com/speed/pagespeed/insights/?hl=de&url=https%3A%2F%2Falexa.20steps.de%2Fde%2Flogin&tab=mobile">mod_pagespeed</a>, Redis, MariaDB Cluster etc. Service status available at <a target="_blank" href="https://monitoring.20steps.de">monitoring.20steps.de</a>

Usage:
* First register at the <a href="https://alexa.20steps.de">20steps Alexa Website</a>.
* Configure the skill by entering the API key of your UptimeRobot account etc.
* Connect the previously registered account in the Alexa App on your smartphone.
* Cp. <a target="_blank" href="https://alexa.20steps.de/en/c/about-the-alexa-skill">About the Alexa skill</a> for more infos.

TODO:
* Provide support for Google Assistant / Actions / API AI / Rich messages (wip)
* Refactor / cleanup Google oAuth hackbup (wip)
* Rename everything to assistant.20steps.de (wip)
* Refactor AbstractCustomBundle of Bricks platform to minimize glue code for user mgmt. and introduce assistantlets into basic layer of Bricks abstracting away Amazon Alexa / Skills and Google Home / Actions
* Publish for Google Assistant
* Prepare Joblet for messages pushed to Alexa by assistantlets (wip)
* A lot more capabilities that help developers, system administrators and marketers
* Adapt for Echo Show as soon as it is available in Germany.
* Automated tests and after that invite some developers to collaborate on speechlets for additional services.

Hints for fellow coders:
* The business logic resides in the https://github.com/20steps/alexa/tree/master/src/Bricks/Custom/Twentysteps/AlexaBrick/AlexaBundle/Modules directory

## Author

* Helmut Hoffer von Ankershoffen (hhva@20steps.de)

## Sponsored by
<a href="https://20steps.de">20steps - Digital Full Service Boutique</a>

p.s.: we are up for hire

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

