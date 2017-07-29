Preparing
=========

Install <a href="http://php.net/">PHP 7</a>
-------------------------------------------

Mac OS X:
```bash
bin/prepare/macos-homebrew
bin/prepare/macos-php7
bin/prepare/macos-bricks-deploy
bin/prepare/macos-gnu-tar
```

Linux / CentOS
```bash
bin/prepare/linux-php7
bin/prepare/linux-bricks-deploy
```


Install <a href="https://getcomposer.org/">Composer</a>
-------------------------------------------------------

Mac OS and Linux:

```bash
bin/prepare/composer
```

Install <a href="https://github.com/travis-ci/travis.rb">Travis CLI</a>
-----------------------------------------------------------------------

Mac OS and Linux:

```bash
bin/prepare/travis-cli
```

Create database user bricks and grant privileges (stage dev)
------------------------------------------------------------

This should only be executed on developer workstations

1. Creates database user "bricks" with password "bricks" and grant privileges on databases with names starting with "bricks".
2. Creates database bricks_platform_dev
3. Sets up database schema for Bricks including Wordpress Multisite

Mac OS and Linux:

```bash
bin/prepare/mysql-dev
bin/console doctrine:migrations:migrate
bin/wp core multisite-install --path="web" --url="admin.platform.bricks.localhost.com" --title="Bricks Platform Standard Edition" --admin_user="admin" --admin_password="bricks" --admin_email="webmaster@20steps.de" 
```

Drop database and start again:

```bash
bin/console doctrine:database:drop --force
```

Create database user bricks and grant privileges (stage test)
------------------------------------------------------------

This is handled automatically.


Create database user bricks and grant privileges (stage live)
------------------------------------------------------------

Get login credentials and database name from your hosting provider and update the following files appropriately:
1. Set credentials in link target of etc/credentials/.credentials (cp. composer.json)
2. Set database host and name in etc/parameters/cluster.generic_live.yml
3. Set credentials, database host and name in link target of etc/credentials/basic/pages/.wordpress (cp. composer.json)

Create schema

```bash
bin/console doctrine:migrations:migrate
bin/wp core multisite-install --path="web" --url="your_domain" --title="your_title" --admin_user="your_wordpress_admin_username" --admin_password="your_wordpress_admin_password" --admin_email="your_wordpress_admin_email_address" 
```
Important notice: Update the second line according to your needs, e.g.

```bash
bin/console doctrine:migrations:migrate
bin/wp core multisite-install --path="web" --url="admin.platform.bricks.20steps.de" --title="Bricks Platform Netzwerk" --admin_user="admin" --admin_password="futuresteps25" --admin_email="webmaster@20steps.de" 
```

Create admin user, first client and first project in Bricks database

```bash
bin/console bricks:core:bootstrap
```

Register local node in database
```bash
bin/console bricks:core:cluster:node:register
```

Check health of Bricks platform
```bash
bin/console bricks:core:info
```

Prepare system services
------------------------

Linux

```bash
bin/prepare/linux-systemctl
```

MacOS X

add

Include /etc/apache2/sites-available/*.conf

to your apache2 config and create the sites-available directory

tbd
