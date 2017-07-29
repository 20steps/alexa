<?php
	
	/**
	Plugin Name: Disable Delete Post
    Contributors: jeremyselph, hhva
	Donate link: http://reactivedevelopment.net/disable-delete-post-page
	Tags: delete post, delete, delete page
	Requires at least: 3.1.1
	Tested up to: 4.1.1
	Stable tag: 4.3
	License: GPLv3 or later
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
	 */

    /**
     * when we activate the plugin do this
     *
     * @package Disable Delete Post or Page
     * @subpackage install_js_disable_delete_post
     * @since 1.0
    */

    function install_js_disable_delete_post() {
        
        $currentJSDeletePostOption          = unserialize( get_option( 'jsDisableDeletePost', '' ) );
        if ( empty( $currentJSDeletePostOption ) ){

            add_option( 'jsDisableDeletePost', 'yes', '', 'yes' );

        }

    }
    
    register_activation_hook( __FILE__, 'install_js_disable_delete_post' );

    /**
     * remove the delete link from the page/posts list
     *
     * @package Disable Delete Post or Page
     * @subpackage js_remove_delete_link_from_post_list
     * @since 1.0
    */
    
    function js_remove_delete_link_from_post_list( $actions, $post ){
        
        // get this posts jsRemoveDeleteLink meta value
        $thisJSDeleteMetaValue              = get_post_meta( $post->ID, '_jsRemoveDeleteLink', true );
        if( $thisJSDeleteMetaValue == 'yes' || $post->ID == 4 ){

            unset( $actions[ 'trash' ] );

        } return $actions;
        
    }
    
    add_filter( 'post_row_actions', 'js_remove_delete_link_from_post_list', 10, 2 );
    add_filter( 'page_row_actions', 'js_remove_delete_link_from_post_list', 10, 2 );

    /**
     * remove the delete link edit post/page page
     *
     * @package Disable Delete Post or Page
     * @subpackage js_remove_delete_link_from_post_edit_page
     * @version 2.0
     * @since 1.0
    */
        
        function js_remove_delete_link_from_post_edit_page(){
        
        /*  when reasearching and looking at wp-admin/includes/meta-boxes.php. There is no way that I can see that will
            allow us to remove the Move to Trash link in the publish box. So this is a temporarry fix untill we can find
            a better way to acomplish this feature. */
            
            $currentJSPostID                = intval( $_GET[ 'post' ] );
            if ( $currentJSPostID > 0 ){
            
                // get this posts jsRemoveDeleteLink meta value
                $thisJSDeleteMetaValue      = get_post_meta( $currentJSPostID, '_jsRemoveDeleteLink', true );
                
                // if value == yes then remove link
                if( $thisJSDeleteMetaValue == 'yes' && ( get_post_type( $currentJSPostID ) == 'page'
                 || get_post_type( $currentJSPostID ) == 'post' ) ) {

                    echo '<style>#delete-action{ display:none; } #js-remove-delete-message{ position:absolute; bottom:11px; }</style>';
                
                }

            }
            
        }
        
        add_action( 'post_submitbox_start', 'js_remove_delete_link_from_post_edit_page' );

    /**
     * add check box to the screen options page
     *
     * @package Disable Delete Post or Page
     * @subpackage js_remove_delete_link_add_checkBox_to_screen_settings
     * @version 2.0
     * @since 1.0
    */
    
    function js_remove_delete_link_add_checkBox_to_screen_settings( $current, $screen ){
        
        /*  found this example in the dont-break-the-code-example */
        $currentJSPostID                    = intval( $_GET[ 'post' ] );
        if ( $currentJSPostID > 0 ){
            
            // if this post is a page or a post then add the check box
            if( in_array( $screen->id, array( 'post', 'page' ) ) && ( get_post_type( $currentJSPostID ) == 'page'
             || get_post_type( $currentJSPostID ) == 'post' ) && current_user_can( 'administrator' ) ){
                
                // get this posts jsRemoveDeleteLink meta value
                $thisJSDeleteMetaValue      = get_post_meta( $currentJSPostID, '_jsRemoveDeleteLink', true );
                
                // if value == yes then add checkbox to the screen settings tab
                $addCheckBoxCode            = '<h5>' . __( 'Remove the ability to delete this' ) . get_post_type( $currentJSPostID ) . '</h5>';
                
                if ( $thisJSDeleteMetaValue == 'yes' ){ $checked = ' checked="checked" '; }
                $addCheckBoxCode           .= '<input type="checkbox" id="jsRemoveDeleteLink" name="jsRemoveDeleteLink"' . $checked . '/>'
                                           .  '<label for="jsRemoveDeleteLink"> '
                                               .  __( 'Remove Trash Link' )
                                           .  '</label> ';

                return $addCheckBoxCode;
                
            } else {
                return null;
            }
        
        }
        
        return null;
        
    }
    
    add_filter( 'screen_settings', 'js_remove_delete_link_add_checkBox_to_screen_settings', 10, 2 );

    /**
     * add jquery function to admin head to save the remove delete link meta for this post
     *
     * @package Disable Delete Post or Page
     * @subpackage js_remove_delete_link_add_jquery_to_head
     * @version 2.0
     * @since 1.0
    */
    
    function js_remove_delete_link_add_jquery_to_head(){
    
        /* add jquery to the head in-order to save the checkbox option */
        $currentJSPostID                    = intval( $_GET[ 'post' ] );
        if ( $currentJSPostID > 0 && current_user_can( 'administrator' ) ) {;
        
        $script = '

            <script type="text/javascript" language="javascript">
                
                jQuery( document ).ready( function(){
                    
                    // when the checkbox is clicked save the meta option for this post
                    jQuery( "#jsRemoveDeleteLink" ).click( function() {
                        
                        var isJSDeleteisChecked = "no";
                        if ( jQuery( "#jsRemoveDeleteLink" ).attr( "checked" ) ){ isJSDeleteisChecked = "yes"; }
                        jQuery.post( ajaxurl,

                            "action=jsRemoveDeleteLink_save&post='.$currentJSPostID.'&jsRemoveDeleteLink=" + isJSDeleteisChecked,
                            
                            function(response) { // hide or show trash link
                                
                                if ( response == "yes" ){ // hide delete link
                                    
                                    jQuery( "#delete-action" ).hide( function() {
                                        
                                        var addThisAboveDelete  = \'<div id="js-remove-delete-message" style="position:absolute; bottom:11px;">\';
                                      
                                        jQuery( addThisAboveDelete ).prependTo( "#major-publishing-actions" );

                                    });

                                } else if ( response == "no" ){ // show delete link
                                    
                                    jQuery( "#js-remove-delete-message" ).remove();
                                    jQuery( "#delete-action" ).show();

                                }

                            });

                        });

                    });

                </script>';
        
    
            echo $script;
        }
        
    }
    
    add_action( 'admin_head', 'js_remove_delete_link_add_jquery_to_head' );

    /**
     * add ajax call to wp in order to save the remove delete post link
     *
     * @package Disable Delete Post or Page
     * @subpackage js_remove_delete_link_add_ajax_call_to_wp
     * @version 2.0
     * @since 1.0
    */
    
    function js_remove_delete_link_add_ajax_call_to_wp(){
        
        /*  found this example in the dont-break-the-code-example */
        $jsRemoveDeleteLink                 = $_POST[ 'jsRemoveDeleteLink' ];
        $currentJSPostID                    = intval( $_POST[ 'post' ] );
	

        if( !empty( $currentJSPostID ) && $jsRemoveDeleteLink !== NULL ) {
            
            
            update_post_meta( $currentJSPostID, '_jsRemoveDeleteLink', $jsRemoveDeleteLink );
            echo $jsRemoveDeleteLink;

        } else { echo $jsRemoveDeleteLink; } exit;
        
    }
    
    add_action( 'wp_ajax_jsRemoveDeleteLink_save', 'js_remove_delete_link_add_ajax_call_to_wp' );

    /**
     * when we deactivate the plugin do this
     *
     * @package Disable Delete Post or Page
     * @subpackage remove_js_disable_delete_post
     * @since 1.0
    */
    
    function remove_js_disable_delete_post() {

        delete_option( 'jsDisableDeletePost' );

    }
    
    register_deactivation_hook( __FILE__, 'remove_js_disable_delete_post' );

