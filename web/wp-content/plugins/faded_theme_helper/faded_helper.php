<?php
/*
Plugin Name: Faded Helper
Plugin URI: http://themes.droitlab.com/wp/faded
Description: Droitthemes theme helper. Custome Post Type generator.
Version: 1.0.0
Author: Droitthemes
Author URI: http://themes.droitlab.com
License: 
Text Domain: faded
*/

/*********************************************
Registers a custom cusotm post 
 ********************************************/
 
global $custom_post_type;
$custom_post_type = array('mixitup');

if(isset($custom_post_type) && !empty($custom_post_type))
{
    foreach($custom_post_type as $key => $val)
    {
        $cpt_name = $val;
        require plugin_dir_path( __FILE__ ).'custom_post_type/dt_'.$cpt_name.'_post.php';
    }
}