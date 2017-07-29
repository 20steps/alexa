<?php

/**
 * Integration with importer/exporter in WooCommerce 3.1+
 */
class WoocommerceGpfImportExportIntegration {

	/**
	 * Constructor.
	 *
	 * Attach to the relevant hooks to integrate with the importer / exporter.
	 */
	public function __construct() {
		// Export filters.
		add_filter( 'woocommerce_product_export_column_names', array( $this, 'add_columns' ) );
		add_filter( 'woocommerce_product_export_product_default_columns', array( $this, 'add_columns' ) );
		$this->attach_render_hooks();
		// Import filters.
		add_filter( 'woocommerce_csv_product_import_mapping_options', array( $this, 'add_columns' ) );
		add_filter( 'woocommerce_csv_product_import_mapping_default_columns', array( $this, 'add_default_mapping_columns' ) );
		add_filter( 'woocommerce_product_import_pre_insert_product_object', array( $this, 'process_import' ), 10, 2 );

	}

	/**
	 * Register our columns with the importer/exporter.
	 *
	 * @param  array  $columns  List of columns.
	 *
	 * @return array            Modified list of columns.
	 */
	public function add_columns( $columns ) {
		return array_merge( $columns, $this->generate_column_list() );
	}

	/**
	 * Attach all necessary hooks for rendering fields during export.
	 */
	private function attach_render_hooks() {
		$fields = $this->generate_column_list();
		foreach ( array_keys( $fields ) as $key ) {
			add_filter( 'woocommerce_product_export_product_column_' . $key, array( $this, "render_column_$key" ), 10, 2 );
		}
	}

	/**
	 * Return list of default mappings.
	 *
	 * @param  array   $mappings  The list of standard mappings.
	 *
	 * @return array             The extended list of mappings.
	 */
	public function add_default_mapping_columns( $mappings ) {
		$fields = $this->generate_column_list();
		foreach ( $fields as $k => $v ) {
			$mappings[ $v ] = $k;
			$mappings[ strtolower( $v ) ] = $k;
		}
		return $mappings;
	}

	/**
	 * Generate a list of our columns from the common field class.
	 *
	 * @return array   Array of GPF columns with appropriate keys.
	 */
	private function generate_column_list() {
		global $woocommerce_gpf_common;
		$fields = wp_list_pluck( $woocommerce_gpf_common->product_fields, 'desc' );
		foreach ( $fields as $key => $value ) {
			$fields[ 'gpf_' . $key ] = sprintf( __( 'Google product feed: %s', 'woocommerce_gpf' ), $value );
			unset( $fields[ $key ] );
		}
		return $fields;
	}

	/**
	 * Process a set of import data.
	 *
	 * @param  WC_Product $object  The product being imported.
	 * @param  array      $data    The data processed from the CSV file and mapped.
	 *
	 * @return WC_Product          The product with updates applied.
	 */
	public function process_import( $object, $data ) {
		$fields       = $this->generate_column_list();
		$product_data = $object->get_meta( '_woocommerce_gpf_data' );
		if ( empty( $product_data ) ) {
			$product_data = array();
		}
		foreach ( array_keys( $fields ) as $key ) {
			if ( isset( $data[ $key ] ) ) {
				$product_data[ str_replace( 'gpf_', '', $key ) ] = $data[ $key ];
			}
		}
		$object->update_meta_data( '_woocommerce_gpf_data', $product_data );
		return $object;
	}

	/**
	 * Magic method to handle the export field rendering.
	 *
	 * Extracts the field name from the method name invoked, and uses
	 * get_product_gpf_value to retrieve the relevant field.
	 *
	 * @param  string  $method  The method name attempted to be called.
	 * @param  array   $args    The args passed to the method.
	 *
	 * @return string           The value for the relevant field.
	 */
	public function __call( $method, $args ) {
		if ( stripos( $method, 'render_column_gpf_' ) !== 0 ) {
			throw new \Exception( 'Invalid method on ' . __CLASS__ );
		}
		$field = str_replace( 'render_column_gpf_', '', $method );
		return $this->get_product_gpf_value( $field, $args[1] );
	}

	/**
	 * Get the value of a GPF field for a product.
	 *
	 * @param  string      $key      The key that we want to retrieve.
	 * @param  WC_Product  $product  The product we're enquiring about.
	 *
	 * @return string                The value of the key for this product, or
	 *                               empty string.
	 */
	private function get_product_gpf_value( $key, $product ) {
		$product_settings = get_post_meta( $product->get_id(), '_woocommerce_gpf_data', true );
		if ( isset( $product_settings[ $key ] ) ) {
			return $product_settings[ $key ];
		}
		return '';
	}
}
