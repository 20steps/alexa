<?php
/*
Plugin Name: Simple Post Notes
Description: Adds simple notes to post, pagem and custom post type edit screen.
Author: Kuba Mikita
Author URI: http://www.wpart.pl
Version: 1.5
License: GPL2
Text Domain: simple-post-notes
Domain Path: /languages
*/

/*
    Copyright (C) 2014  Kuba Mikita  jakub@underdev.it

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

// Constants
define( 'SPNOTES', plugin_dir_url( __FILE__ ) );
define( 'SPNOTES_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Simple Post Notes class
 */
class SPNotes {

	/**
	 * Plugin settings
	 * @var array
	 */
	public $settings = array();

	/**
	 * Settings page hook
	 * @var string
	 */
	public $page_hook;

	/**
	 * Default post types
	 * @var array
	 */
	public static $default_post_types = array( 'post', 'page' );

	/**
	 * Class constructor
	 */
	public function __construct() {

		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

		register_activation_hook( __FILE__, array( 'SPNotes', 'activation' ) );
		register_uninstall_hook( __FILE__, array( 'SPNotes', 'uninstall' ) );

		add_action( 'admin_menu', array( $this, 'register_page' ), 8, 0 );
		add_action( 'admin_init', array( $this, 'register_settings' ), 10, 0 );

		add_action( 'admin_init', array( $this, 'add_columns' ), 10, 0 );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_and_styles' ), 10 , 1 );

		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );

		add_action( 'save_post', array( $this, 'save_note' ) );

	}

	/**
	 * Loads textdomain
	 * @return void
	 */
	public function load_textdomain() {

		load_plugin_textdomain( 'simple-post-notes', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	}

	/**
	 * On plugin activation
	 * @return void
	 */
	public static function activation() {

		add_option( 'spnotes_settings', array(
			'post_types' => self::$default_post_types
		) );

	}

	/**
	 * On plugin uninstall
	 * @return void
	 */
	public static function uninstall() {

		delete_option( 'spnotes_settings' );

	}

	/**
	 * Adds columns
	 * @return  void
	 */
	public function add_columns() {

		if ( apply_filters( 'spn/columns-display', true ) ) {
			$this->add_column_filters();
		}

	}

	public function add_column_filters() {

		$this->settings = get_option( 'spnotes_settings' );

		foreach ( $this->settings['post_types'] as $post_type ) {

			if ( $post_type == 'page' && apply_filters( 'spn/columns-display/page', true ) ) {

				add_filter( 'manage_pages_columns', array( $this, 'add_column' ) );
				add_action( 'manage_pages_custom_column', array( $this, 'output_column' ), 10, 2 );

			} else if ( apply_filters( 'spn/columns-display/' . $post_type, true ) ) {

				add_filter( 'manage_' . $post_type . '_posts_columns', array( $this, 'add_column' ) );
				add_action( 'manage_' . $post_type . '_posts_custom_column', array( $this, 'output_column' ), 10, 2 );

			}

			if ( apply_filters( 'spn/columns-sortable/' . $post_type, true ) ) {
				add_filter( 'manage_edit-' . $post_type . '_sortable_columns', array( $this, 'register_sortable_column' ) );
			}

		}

	}

	/**
	 * Adds column with note to posts table
	 * @param   array columns current columns
	 * @return  array columns
	 */
	public function add_column( $columns ) {

		$insertKey = 'title';

		$keys = array_keys( $columns );
		$vals = array_values( $columns );

		$insertAfter = array_search( $insertKey, $keys ) + 1;

		$keys2 = array_splice( $keys, $insertAfter );
		$vals2 = array_splice( $vals, $insertAfter );

		$keys[] = 'spnote';
		$vals[] = __( 'Notes', 'simple-post-notes' );

		return array_merge( array_combine( $keys, $vals ), array_combine( $keys2, $vals2 ) );

	}

	/**
	 * Registers added column as sortable
	 * @param   array columns current columns
	 * @return  array columns
	 */
	public function register_sortable_column( $columns ) {

		$columns['spnote'] = 'spnote';
	    return $columns;

	}

	/**
	 * Outputs column content
	 * @param string $column  current column slug
	 * @param int    $post_id current post ID
	 */
	public function output_column( $column, $post_id ) {

		if ( $column == 'spnote' ) {

			$note = get_post_meta( $post_id, '_spnote', true );

			if ( $note ) {

				echo nl2br( esc_attr( $note ) );

			}

		}

	}

	/**
	 * Adds metabox to edit post screen
	 * @return  void
	 */
	public function add_meta_box() {

		foreach ( $this->settings['post_types'] as $screen ) {

			add_meta_box(
				'spnotes',
				__( 'Notes', 'simple-post-notes' ),
				array( $this, 'metabox' ),
				$screen,
				'side',
				'high'
			);
		}
	}

	/**
	 * Displays metabox content
	 * @param  object $post current WP_Post object
	 * @return void
	 */
	public function metabox( $post ) {

		wp_nonce_field( 'spnotes_note_' . $post->ID, 'spnotes_nonce' );

		$note = get_post_meta( $post->ID, '_spnote', true );

		echo '<textarea style="display: block; width: 100%;" rows="5" name="spnote" />' . esc_html( $note ) . '</textarea>';

	}

	/**
	 * Saves the note
	 * @param  int $post_id saved post ID
	 * @return void
	 */
	public function save_note( $post_id ) {

		if ( ! isset( $_POST['spnotes_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['spnotes_nonce'], 'spnotes_note_' . $post_id ) ) {
			return;
		}

		if ( ! isset( $_POST['spnote'] ) ) {
			return;
		}

		update_post_meta( $post_id, '_spnote', $_POST['spnote'] );

	}

	/**
	 * Registers admin page
	 * @return  void
	 */
	public function register_page() {

		$this->page_hook = add_options_page(
			__( 'Post Notes', 'simple-post-notes' ),
			__( 'Post Notes', 'simple-post-notes' ),
			'manage_options',
			'spnotes',
			array( $this, 'display_settings_page' )
		);

	}

	/**
	 * Displays settings page
	 * @return  void
	 */
	public function display_settings_page() {
	?>

		<div class="wrap">

			<h2><?php _e( 'Simple Post Notes Settings', 'simple-post-notes' ); ?></h2>

			<form action="options.php" method="post" enctype="multipart/form-data">
				<?php settings_fields( 'spnotes_settings' ); ?>
				<?php do_settings_sections( 'spnotes' ); ?>
				<?php submit_button( __( 'Save', 'spnotes' ), 'primary', 'save' ); ?>
			</form>

		</div>

	<?php
	}

	/**
	 * Registers settings
	 * @return void
	 */
	public function register_settings() {

		$this->settings = get_option( 'spnotes_settings' );

		register_setting( 'spnotes_settings', 'spnotes_settings' );

		add_settings_section(
			'spnotes_general',
			__( 'General Settings', 'simple-post-notes' ),
			null,
			'spnotes'
		);

	 	add_settings_field(
			'post_types',
			__( 'Post types', 'simple-post-notes' ),
			array( $this, 'settings_post_type_field' ),
			'spnotes',
			'spnotes_general'
		);

	}

	/**
	 * Settings fields
	 *
	 * Post type field output
	 *
	 * @access  public
	 *
	 * @return  void
	 */
	public function settings_post_type_field() {

		if ( ! isset( $this->settings['post_types'] ) || empty( $this->settings['post_types'] ) ) {
			$this->settings['post_types'] = self::$default_post_types;
		}

		echo '<select multiple="multiple" name="spnotes_settings[post_types][]" id="post_types" class="chosen-select" style="width: 300px;">';

			foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $post_type ) {

				if ( $post_type->name == 'attachment' ) {
					continue;
				}

				$selected = in_array( $post_type->name, $this->settings['post_types'] ) ? 'selected="selected"' : '';
				echo '<option value="' . $post_type->name . '" ' . $selected . '>' . $post_type->labels->name . '</option>';

			}

		echo '</select>';

		echo '<p class="description">'.__( 'Apply Post Notes to these post types', 'simple-post-notes' ).'</p>';

	}

	/**
	 * Enqueue scripts and styles
	 * @param  string $hook current page hook
	 * @return void
	 */
	public function enqueue_scripts_and_styles( $hook ) {

		if ( $this->page_hook != $hook || $hook != 'post-new.php' || $hook != 'post.php' ) {
			// return;
		}

		// enqueue scripts

		wp_enqueue_script( 'spnotes/chosen', SPNOTES . 'assets/chosen/chosen.jquery.min.js', array(
			'jquery'
		), false, true );

		wp_enqueue_script( 'spnotes/admin', SPNOTES . 'assets/admin.js', array(
			'jquery',
			'spnotes/chosen'
		), false, true );


		// enqueue styles

		wp_enqueue_style( 'spnotes/chosen', SPNOTES . 'assets/chosen/chosen.min.css' );

		// wp_enqueue_style( 'spnotes/admin', SPNOTES . 'assets/admin.css' );

	}

}

new SPNotes();
