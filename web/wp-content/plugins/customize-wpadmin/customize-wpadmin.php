<?php
/**
 * Plugin Name: Custom Links On Admin Dashboard Toolbar
 * Plugin URI: http://barkalov.com/
 * Description: This plugin allows you to add custom links and menus to the WordPress administrative interface. By adding custom links, you can make WordPress admin into the center of your admin universe. A typical use case would involve adding the link to your web host's CPanel.
 * Version: 3.2
 * Author: Victor Barkalov
 * Author URI: http://barkalov.com/
 * 
 * License: GPL2
 */

/*  Copyright 2014  Victor Barkalov

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

add_action('admin_menu', 'customizewpadmin_init');


function customizewpadmin_init() {
	// Create SQL table
	global $wpdb;
	$table_name = $wpdb->prefix . "customizeadmin"; 
	$sql = "CREATE TABLE $table_name (
		id mediumint not null auto_increment,
		parent_slug varchar(255),
		menu_title varchar(255),
		capability varchar(255),
		menu_slug varchar(255),
		parent_id int,
		icon varchar(255) NULL,
		order_index mediumint null,
		PRIMARY KEY  (id))";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
	dbDelta("ALTER TABLE `$table_name` ADD `icon` VARCHAR(255) NULL ;");
	dbDelta("ALTER TABLE `$table_name` ADD `order_index` mediumint null;");
	$wpdb->query("update `$table_name` set `order_index` = `id` where `order_index` is null;");

	// Save changes to menu items
	if (isset($_POST["CustomizeWPAdmin_Cmd"]) && $_POST["CustomizeWPAdmin_Cmd"] == "Save") customizewpadmin_save();
	// Create menu items
	add_submenu_page('options-general.php', 'Customize Admin Interface', 'Customize Admin Interface', 'edit_dashboard', 'customize-wpadmin-admin', 'customizewpadmin_admin');
	customizewpadmin_createcustommenus();
}

function customizewpadmin_createcustommenus() {
	// Add customized admin hooks
	$links = customizewpadmin_getlinks("");
	foreach ( $links as $link ) {
		if ($link->icon == "") $link->icon = "admin-generic";
		if ($link->parent_id === null) {
			add_menu_page($link->menu_title, $link->menu_title, $link->capability, 'custom_menu_' . $link->id, 'customizewpadmin_redirect', 'dashicons-' . $link->icon);
		} else {
			add_submenu_page('custom_menu_' . $link->parent_id, $link->menu_title, $link->menu_title, $link->capability, 'custom_menu_' . $link->id, 'customizewpadmin_redirect');
		}
	}
}

function customizewpadmin_redirect() {
	$page = $_GET['page'];
	$page = substr($page, 12);
	global $wpdb;
	$table_name = $wpdb->prefix . "customizeadmin"; 
	global $customizewpadmin_redirect_url;
	$customizewpadmin_redirect_url = $wpdb->get_var("SELECT menu_slug from $table_name where id = " . $page);
	set_error_handler('customizewpadmin_redirect_js', E_WARNING);
	header('Location: ' . $customizewpadmin_redirect_url);
}

function customizewpadmin_redirect_js() {
	global $customizewpadmin_redirect_url;
	echo "<script>window.location = '$customizewpadmin_redirect_url';</script>";
}

function customizewpadmin_save() {
	global $wpdb;
	$table_name = $wpdb->prefix . "customizeadmin";

	if ($_POST["CustomizeWPAdmin_Cmd2"] == "MoveUp" || $_POST["CustomizeWPAdmin_Cmd2"] == "MoveDn") {
		$id = intval($_POST["CustomizeWPAdmin_Cmd2Id"]);
		$link = $wpdb->get_row("select id, parent_id, order_index from $table_name where id = $id");
		$other = array();
		if ($_POST["CustomizeWPAdmin_Cmd2"] == "MoveUp")
			$other = $wpdb->get_row("select order_index, id from $table_name where ifnull(parent_id, '') = '" . $link->parent_id . "' and order_index < " . $link->order_index . " order by order_index desc limit 1");
		else
			$other = $wpdb->get_row("select order_index, id from $table_name where ifnull(parent_id, '') = '" . $link->parent_id . "' and order_index > " . $link->order_index . " order by order_index asc limit 1");
		if (is_object($other) && isset($other->id) && $other->id != "") {
			$wpdb->query("update $table_name set order_index = " . $other->order_index . " where id = " . $link->id);
			$wpdb->query("update $table_name set order_index = " . $link->order_index . " where id = " . $other->id);
		}
	}

	foreach($_POST["CustomMenu_ID"] as $id) {
		$sql = "";
		if ($id == "0" && $_POST["CustomMenu_Title"][$id] != "") {
			$wpdb->query($wpdb->prepare("insert into $table_name(menu_title, capability, menu_slug, icon, parent_id) values(%s, %s, %s, %s, nullif(%s, ''))",
				$_POST["CustomMenu_Title"][$id],
				$_POST["CustomMenu_Capability"][$id],
				$_POST["CustomMenu_URL"][$id],
				$_POST["CustomMenu_Icon"][$id],
				$_POST["CustomMenu_ParentID"][$id]
				));
			$wpdb->query($wpdb->prepare("update `$table_name` set `order_index` = `id` where `order_index` is null;"));
		} else if ($_POST["CustomMenu_Cmd"][$id] == "Delete") {
			$wpdb->query($wpdb->prepare("delete from $table_name where id = $id"));
		} else if ($id != "0") {
			$wpdb->query($wpdb->prepare("update $table_name set menu_title = %s, capability = %s, menu_slug = %s, icon = %s, parent_id = nullif(%s, '') where id = $id",
				$_POST["CustomMenu_Title"][$id],
				$_POST["CustomMenu_Capability"][$id],
				$_POST["CustomMenu_URL"][$id],
				$_POST["CustomMenu_Icon"][$id],
				$_POST["CustomMenu_ParentID"][$id]
				));
		}
	}
	//wp_enqueue_script('customize-wpadmin-refresh', plugins_url( 'refresh.js', __FILE__ ));
}

$customizewpadmin_icons = "
	menu 
	,admin-site 
	,dashboard 
	,admin-post 
	,admin-media 
	,admin-links 
	,admin-page 
	,admin-comments 
	,admin-appearance 
	,admin-plugins 
	,admin-users 
	,admin-tools 
	,admin-settings 
	,admin-network 
	,admin-home 
	,admin-generic 
	,admin-collapse 
	,welcome-write-blog 
	,welcome-add-page 
	,welcome-view-site 
	,welcome-widgets-menus 
	,welcome-comments 
	,welcome-learn-more 
	,format-aside 
	,format-image 
	,format-gallery 
	,format-video 
	,format-status 
	,format-quote 
	,format-chat 
	,format-audio 
	,camera 
	,images-alt 
	,images-alt2 
	,video-alt 
	,video-alt2 
	,video-alt3 
	,image-crop 
	,image-rotate-left 
	,image-rotate-right 
	,image-flip-vertical 
	,image-flip-horizontal 
	,undo 
	,redo 
	,editor-bold 
	,editor-italic 
	,editor-ul 
	,editor-ol 
	,editor-quote 
	,editor-alignleft 
	,editor-aligncenter 
	,editor-alignright 
	,editor-insertmore 
	,editor-spellcheck 
	,editor-distractionfree 
	,editor-kitchensink 
	,editor-underline 
	,editor-justify 
	,editor-textcolor 
	,editor-paste-word 
	,editor-paste-text 
	,editor-removeformatting 
	,editor-video 
	,editor-customchar 
	,editor-outdent 
	,editor-indent 
	,editor-help 
	,editor-strikethrough 
	,editor-unlink 
	,editor-rtl 
	,align-left 
	,align-right 
	,align-center 
	,align-none 
	,lock 
	,calendar 
	,visibility 
	,post-status 
	,edit 
	,trash 
	,arrow-up 
	,arrow-down 
	,arrow-right 
	,arrow-left 
	,arrow-up-alt 
	,arrow-down-alt 
	,arrow-right-alt 
	,arrow-left-alt 
	,arrow-up-alt2 
	,arrow-down-alt2 
	,arrow-right-alt2 
	,arrow-left-alt2 
	,sort 
	,leftright 
	,list-view 
	,exerpt-view 
	,share 
	,share-alt 
	,share-alt2 
	,twitter 
	,rss 
	,facebook 
	,facebook-alt 
	,googleplus 
	,networking 
	,hammer 
	,art 
	,migrate 
	,performance 
	,wordpress 
	,wordpress-alt 
	,pressthis 
	,update 
	,screenoptions 
	,info 
	,cart 
	,feedback 
	,cloud 
	,translation 
	,tag 
	,category 
	,yes 
	,no 
	,no-alt 
	,plus 
	,minus 
	,dismiss 
	,marker 
	,star-filled 
	,star-half 
	,star-empty 
	,flag 
	,location 
	,location-alt 
	,vault 
	,shield 
	,shield-alt 
	,search 
	,slides 
	,analytics 
	,chart-pie 
	,chart-bar 
	,chart-line 
	,chart-area 
	,groups 
	,businessman 
	,id 
	,id-alt 
	,products 
	,awards 
	,forms 
	,portfolio 
	,book 
	,book-alt 
	,download 
	,upload 
	,backup 
	,lightbulb 
	,smiley";

$customizewpadmin_capabilities = "
	activate_plugins
	,delete_others_pages
	,delete_others_posts
	,delete_pages
	,delete_plugins
	,delete_posts
	,delete_private_pages
	,delete_private_posts
	,delete_published_pages
	,delete_published_posts
	,edit_dashboard
	,edit_files
	,edit_others_pages
	,edit_others_posts
	,edit_pages
	,edit_posts
	,edit_private_pages
	,edit_private_posts
	,edit_published_pages
	,edit_published_posts
	,edit_theme_options
	,export
	,import
	,list_users
	,manage_categories
	,manage_links
	,manage_options
	,moderate_comments
	,promote_users
	,publish_pages
	,publish_posts
	,read_private_pages
	,read_private_posts
	,read
	,remove_users
	,switch_themes
	,upload_files
	,update_core
	,update_plugins
	,update_themes
	,install_plugins
	,install_themes
	,delete_themes
	,edit_plugins
	,edit_themes
	,edit_users
	,create_users
	,delete_users
	,unfiltered_html";

function customizewpadmin_getlinks($parent) {
	global $wpdb;
	$table_name = $wpdb->prefix . "customizeadmin"; 
	$results = array();
	$links = $wpdb->get_results("
		SELECT 
			links.* 
		from $table_name as links 
		where ifnull(parent_id, '') = '$parent'
		order by order_index
		");
	foreach($links as $link) {
		$results[] = $link;
		$children = customizewpadmin_getlinks($link->id);
		if (sizeof($children) >= 1) $results = array_merge($results, $children);
	}
	return $results;
}

function customizewpadmin_admin() {
	global $customizewpadmin_icons;
	global $customizewpadmin_capabilities;
	global $wpdb;
	$table_name = $wpdb->prefix . "customizeadmin"; 

	wp_enqueue_script('customize-wpadmin-admin-js', plugins_url( 'admin.js', __FILE__ ));
	wp_enqueue_style('customize-wpadmin-admin-css', plugins_url( 'admin.css', __FILE__ ));

	$links = customizewpadmin_getlinks("");
	$links[] = (object) array(
				'id' => 0, 
				'capability' => "edit_dashboard", 
				"parent_id" => "",
				"menu_title" => "",
				"menu_slug" => "",
				"icon" => ""
			);
	$parents = $wpdb->get_results("SELECT * from $table_name where parent_id is null order by menu_title");
	array_unshift($parents, (object) array('id' => "", 'menu_title' => "-- None --"));
	echo("<form method='post' id='CustomizeWPAdmin'>");
	echo("<input type='hidden' name='CustomizeWPAdmin_Cmd2' value='' />");
	echo("<input type='hidden' name='CustomizeWPAdmin_Cmd2Id' value='' />");
	echo("<input type='hidden' name='CustomizeWPAdmin_Cmd' value='Save' />");
	echo("<h1>Add/Edit/Delete Links in WordPress Admin Menu</h1>");
	echo("<table border='1' cellspacing='0' cellpadding='3' class='customizewpadmin_links'>");
	echo("<tr><th></th><th>ID</th><th>Menu Title</th><th>Required Capability<br />to Use This Menu</th><th>URL</th><th>Parent</th><th>Icon<br />(Note: WordPress will not show icons for submenus)</th></tr>");
	foreach ( $links as $link ) {
		echo("<tr id='CustomMenu_TR[" . $link->id . "]'>");
		if ($link->id >= 1) {
			echo("
				<td style='white-space: nowrap;'>
					<a href='javascript:DeleteCustomMenu(" . $link->id . ")'>Delete</a>&nbsp;&nbsp;
					<a href='javascript:MoveUpCustomMenu(" . $link->id . ")'>Up</a>&nbsp;&nbsp;
					<a href='javascript:MoveDnCustomMenu(" . $link->id . ")'>Dn</a>&nbsp;&nbsp;
				</td>");
			echo("<td>" . $link->id . "<input type='hidden' name='CustomMenu_Cmd[" . $link->id . "]' value='' /><input type='hidden' name='CustomMenu_ID[" . $link->id . "]' value='" . $link->id . "' /></td>");
		} else {
			echo("<td><i>New</i></td>");
			echo("<td><i>New</i><input type='hidden' name='CustomMenu_Cmd[" . $link->id . "]' value='' /><input type='hidden' name='CustomMenu_ID[" . $link->id . "]' value='" . $link->id . "' /></td>");
		}
		if ($link->parent_id == "") {
			echo("<td><input style='width: 190px;' type='text' name='CustomMenu_Title[" . $link->id . "]' value='" . htmlentities($link->menu_title, ENT_QUOTES) . "' /></td>");
		} else {
			echo("<td><span style='font-weight: 900; font-size: 20px; display: inline-block; text-align: center; width: 40px;'>&rdsh;</span><input style='width: 150px;' type='text' name='CustomMenu_Title[" . $link->id . "]' value='" . htmlentities($link->menu_title, ENT_QUOTES) . "' /></td>");
		}
		echo("<td><select name='CustomMenu_Capability[" . $link->id . "]'>");
		foreach(explode(',', $customizewpadmin_capabilities) as $capability) {
			$capability = trim($capability);
			if ($link->capability == $capability)
				echo("<option value='$capability' selected='selected'>$capability</option>");
			else
				echo("<option value='$capability'>$capability</option>");
		}
		echo("</select></td>");
		echo("<td><input type='text' name='CustomMenu_URL[" . $link->id . "]' value='" . htmlentities($link->menu_slug, ENT_QUOTES) . "' /></td>");

		echo("<td><select name='CustomMenu_ParentID[" . $link->id . "]'>");
		foreach($parents as $parent) {
			if ($parent->id != $link->id || $parent->id == "") {
				if ($link->parent_id == $parent->id)
					echo("<option value='" . $parent->id . "' selected='selected'>" . $parent->menu_title . "</option>");
				else
					echo("<option value='" . $parent->id . "'>" . $parent->menu_title . "</option>");
			}
		}
		echo("</select></td>");
		//echo("<td><input style='width: 50px;' type='text' name='CustomMenu_ParentID[" . $link->id . "]' value='" . $link->parent_id . "' /></td>");

		if ($link->icon == "") $link->icon = "admin-generic";
		echo("<td><input name='CustomMenu_Icon[" . $link->id . "]' value='" . $link->icon . "' type='hidden'>");
		echo("<div id='CustomMenu_IconPreview[" . $link->id . "]' class='dashicons dashicons-" . $link->icon . "' style='margin: 5px; cursor: pointer;'></div>");
		echo("<a href='javascript:ShowHideIconOptions(" . $link->id . ")'>Show/Hide Images</a><div id='CustomMenu_IconOptions[" . $link->id . "]' style='display: none; padding-right: 50px;'>");
		foreach(explode(',', $customizewpadmin_icons) as $icon) {
			$icon = trim($icon);
			if ($link->icon == $icon)
				echo("<div value='$icon' onclick='SelectIcon(" . $link->id . ", \"$icon\")'  class='dashicons dashicons-$icon' style='margin: 5px; cursor: pointer; border: solid 3px #0000ff;'></div>");
			else
				echo("<div value='$icon' onclick='SelectIcon(" . $link->id . ", \"$icon\")' class='dashicons dashicons-$icon' style='margin: 5px; cursor: pointer;'></div>");
		}
		echo("</div></td>");
		echo("</tr>");
	}
	echo("</table>");
	echo("<br /><br /><input type='submit' onclick='' value='Save All Changes' />");
	echo("</form>");
}
?>
