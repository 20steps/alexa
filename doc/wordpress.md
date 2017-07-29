Wordpress
=========

Download
--------

```bash
bin/wp core download --locale="de_DE"
rm web/index.php web/license.txt web/liesmich.html web/readme.html
```

Install
-------

```bash
bin/wp core multisite-install --url="admin.platform.bricks.localhost.com" --title="Bricks Platform Standard Edition" --admin_user="admin" --admin_password="bricks" --admin_email="webmaster@20steps.de"
```

Add basic plugins

```bash
bin/wp plugin install wordpress-mu-domain-mapping --activate-network
cp web/wp-content/plugins/wordpress-mu-domain-mapping/sunrise.php web/wp-content/sunrise.php
```

Run
----

If you have a webserver running on port 80 terminate it.

Execute to start built-in webserver on port 80 (required for Wordpress usage):
```bash
sudo bin/console server:run --port=80
```

Goto 
* <a href="http://localhost.com/wp-admin">Wordpress Admin Dashboard</a>
* <a href="http://localhost.com/">Symfony start page</a>

Update
------

```bash
bin/wp core update
```

Additional Plugins
------------------

```bash
bin/wp plugin install contact-form-7
bin/wp plugin install exclude-pages
bin/wp plugin install cookie-law-infob
bin/wp plugin install google-analytics-for-wordpress
bin/wp plugin install insert-headers-and-footers
bin/wp plugin install instagram-feed
bin/wp plugin install popupally
bin/wp plugin install pretty-link
bin/wp plugin install public-post-preview
bin/wp plugin install responsive-lightbox
bin/wp plugin install responsive-lightbox
bin/wp plugin install shariff
bin/wp plugin install simple-301-redirects
bin/wp plugin install statify
bin/wp plugin install wordpress-seo
bin/wp plugin install wp-pagenavi
bin/wp plugin install yop-poll
bin/wp plugin install yuzo-related-post

bin/wp plugin install wp-mail-smtp
bin/wp plugin install user-role-editor
bin/wp plugin install multisite-plugin-manager
bin/wp plugin install wp-less
bin/wp plugin install unconfirmed
```
Open for ecke:
- connects-cleverreach
- convertplug
- geodir*



Install missiong plugins
------------------------
```bash
bin/wp plugin install-missing --dry-run
```

Create additional sites
-----------------------

```bash
bin/wp site create --slug="bei-mir-um-die-ecke"
bin/wp site create --slug="demo-acme"
bin/wp site create --slug="giga-smb"
```

Activate some plugins
---------------------

```bash
bin/wp plugin activate your_plugin --url="url_of_site"
```

Add bin/wp packages
--------------------
```bash
bin/wp package install YOUR_PACKAGE
```

Goto <a href="http://wp-cli.org/package-index/">Package Repository</a>.

Hint: packages are installed into bin/.wp as configured in bin/wp

Migrate existing Wordpress Website into Multisite network
----------------------------------------------------------

* Create new site in network (cp. above) - ID of new site will be shown
* Copy wp-content/uploads/* (single-site) to wp-content/uploads/sites/ID/ in network
* Export single-site database, rename wp_ tables to wp_ID_
* Import wp_ID_tables into multisite db (excluding wp_ID_users and wp_ID_usermeta)
* Rename wp_ID_options -> option_name==wp_user_roles -> option_name wp_ID_user_roles (otherwise superadmin cannot log into to YOUR_DOMAIN/wp-admin)
* Use search-and-replace to replace to adapt domain
* Do the domain-adapt-stuff in wp_blogs, wp_ID_ooptions, wp_domain_mapping
* Add to web/wp-content/plugins and web/wp-content/themes as needed



