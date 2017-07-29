=== Plugin Name ===
Contributors: vbarkalov
Tags: links, menu, admin, add, wpadmin, custom, navigation, administration, customize, toolbar, dashboard
Requires at least: 2.7.0
Tested up to: 4.1
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin allows you to add custom links and menus to the WordPress admin dashboard.

== Description ==

This plugin allows you to add custom links and menus to the toolbar in the WordPress admin dashboard. By adding custom links, you can make the WordPress administrative section into the center of your admin universe. A typical use case would involve adding the link to your web host's CPanel.

== Installation ==

Use the standard installation and activation procedure.

1. Unzip the plugin to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to "Settings" and then to "Customize Admin Interface" to set up the plugin.

== Screenshots ==

1. This is what your custom link looks like in the admin toolbar. You can create main menu items as well as submenu items.
1. Admin interface allows you to add new custom links to WordPress admin menu toolbar.
1. Select a descriptive icon from the WordPress icon collection to represent your custom link.

== Changelog ==

= 3.2 =
* Bug fix: no longer show a PHP notice when redirecting using JavaScript instead of a header.

= 2.8 - 3.1 =
* Bug fix: misc. issues with the upgrade from 2.6 to 2.7.

= 2.7 =
* Enhancement: added ability to reorder links.
* Enhancement: eliminated the need to refresh the page after saving changes before changes are reflected.
* Bug fix: certain SQL statements used to have hardcoded "wp_" table prefix.
* Security fix: SQL statements that use parameters now pass them as placeholders for security reasons.

= 2.6 =
* Bug fix: the admin interface used to allow selecting a menu item as a parent menu of itself.

= 2.5 =
* Enhancement: improved the admin interface when creating submenu items and movings submenu items between main menu items.

= 2.4 =
* Bug fix: the updated versions of JS and CSS files were not included.

= 2.2 & 2.3 =
* Bug fix: resolved issues with misnumbered versions.

= 2.1 =
* Bug fix: the SQL tables were not being upgraded when upgrading the plugin from version 1.x to version 2.x.

= 2.0 =
* Enhancement: added ability to select an icon for each new custom menu item from the WordPress icon collection.

= 1.1 =
* Bug fix: the menus were not refreshed automatically after a new link was added.

= 1.0 =
* First stable version of the plugin.
