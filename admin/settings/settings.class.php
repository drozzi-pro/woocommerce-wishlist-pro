<?php
/**
 * Admin settings class
 *
 * @since             1.17.0
 * @package           TInvWishlist\Admin
 * @subpackage        Settings
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Admin settings class
 */
class TInvWL_Admin_Settings_Settings extends TInvWL_Admin_BaseSection {

	/**
	 * Priority for admin menu
	 *
	 * @var integer
	 */
	public $priority = 150;

	/**
	 * This class
	 *
	 * @var \TInvWL_Admin_Settings_Settings
	 */
	protected static $_instance = null;

	/**
	 * Get this class object
	 *
	 * @param string $plugin_name Plugin name.
	 *
	 * @return \TInvWL_Admin_Settings_Settings
	 */
	public static function instance( $plugin_name = TINVWL_PREFIX, $plugin_version = TINVWL_VERSION ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $plugin_name, $plugin_version );
		}

		return self::$_instance;
	}

	/**
	 * Menu array
	 *
	 * @return array
	 */
	function menu() {
		return array(
			'title'      => __( 'Export/Import Settings', 'ti-woocommerce-wishlist-premium' ),
			'page_title' => __( 'Export/Import Plugin Settings', 'ti-woocommerce-wishlist-premium' ),
			'method'     => array( $this, '_print_' ),
			'slug'       => 'export-import-settings',
			'capability' => 'tinvwl_export_import_settings',
		);
	}
}
