Testing
=======

Startup services
----------------

If basic services ae not yet running start them now:

```bash
bin/console bricks:found:setup
bin/vendor/solr &
bin/console bricks:core:job:run &
```

After services up and running you might want to update the Solr index:

```bash
bin/console bricks:found:process
```

Start tests using all integrated testing frameworks
---------------------------------------------------

Execute:

```bash
composer test
```

... or by using <a href="https://phpunit.de/">phpunit</a>
---------------------------------------------------------

Execute:

```bash
composer test-phpunit
```

... or by using <a href="http://behat.org/en/latest/">Behat</a>
----------------------------------------------------------------

Execute:

```bash
composer test-behat
```

... and after all local tests are green using <a href="https://travis-ci.org">Travis</a>
----------------------------------------------------------------------------------------

Execute:

```bash
git push origin master
```

After a successful test this will auto-deploy to the target specified in the "deploy" section of your  .travis.yml

Setup Travis (only once per project)
------------------------------------

```bash
travis login --pro
travis sshkey --pro --generate -r 20steps/bricks-platform-standard-edition
```

Trigger travis as part of continuous integration:

