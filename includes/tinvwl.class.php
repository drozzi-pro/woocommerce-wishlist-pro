<?php
/**
 * Run plugin class
 *
 * @since             1.0.0
 * @package           TInvWishlist
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Run plugin class
 */
class TInvWL {

	/**
	 * Plugin name
	 *
	 * @var string
	 */
	private $_name;
	/**
	 * Plugin version
	 *
	 * @var string
	 */
	private $_version;
	/**
	 * Admin class
	 *
	 * @var TInvWL_Admin_TInvWL
	 */
	public $object_admin;
	/**
	 * Public class
	 *
	 * @var TInvWL_Public_TInvWL
	 */
	public $object_public;
	/**
	 * Array of deprecated hook handlers.
	 *
	 * @var array of WC_Deprecated_Hooks
	 */
	public $deprecated_hook_handlers = array();

	/**
	 * Constructor
	 * Created admin and public class
	 */
	function __construct() {
		$this->_name    = TINVWL_PREFIX;
		$this->_version = TINVWL_VERSION;

		$this->set_locale();

		$this->load_function();
		$this->define_hooks();
		$this->object_admin = new TInvWL_Admin_TInvWL( $this->_name, $this->_version );

		// Allow to disable wishlist for frontend conditionally. Must be hooked on 'plugins_loaded' action.
		if ( apply_filters( 'tinvwl_load_frontend', true ) ) {
			$this->object_public = TInvWL_Public_TInvWL::instance( $this->_name, $this->_version );
		}
	}

	/**
	 * Run plugin
	 */
	function run() {
		TInvWL_View::_init( $this->_name, $this->_version );
		TInvWL_Form::_init( $this->_name );

		if ( is_admin() ) {
			new TInvWL_WizardSetup( $this->_name, $this->_version );
			new TInvWL_Export( $this->_name, $this->_version );
			$this->object_admin->load_function();
		} else {
			// Allow to disable wishlist for frontend conditionally. Must be hooked on 'plugins_loaded' action.
			if ( apply_filters( 'tinvwl_load_frontend', true ) ) {
				$this->object_public->load_function();
			}
		}

		$this->deprecated_hook_handlers['actions'] = new TInvWL_Deprecated_Actions();
		$this->deprecated_hook_handlers['filters'] = new TInvWL_Deprecated_Filters();
		$this->rest_api                            = TInvWL_API::init();
	}

	/**
	 * Set localization
	 */
	private function set_locale() {
		if ( function_exists( 'determine_locale' ) ) {
			$locale = determine_locale();
		} else {
			$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		}

		$locale = apply_filters( 'plugin_locale', $locale, TINVWL_DOMAIN );

		$mofile  = sprintf( '%1$s-%2$s.mo', TINVWL_DOMAIN, $locale );
		$mofiles = array();

		$mofiles[] = WP_LANG_DIR . DIRECTORY_SEPARATOR . basename( TINVWL_PATH ) . DIRECTORY_SEPARATOR . $mofile;
		$mofiles[] = WP_LANG_DIR . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $mofile;
		$mofiles[] = TINVWL_PATH . 'languages' . DIRECTORY_SEPARATOR . $mofile;
		foreach ( $mofiles as $mofile ) {
			if ( file_exists( $mofile ) && load_textdomain( TINVWL_DOMAIN, $mofile ) ) {
				return;
			}
		}

		load_plugin_textdomain( TINVWL_DOMAIN, false, basename( TINVWL_PATH ) . DIRECTORY_SEPARATOR . 'languages' );
	}

	/**
	 * Define hooks
	 */
	function define_hooks() {
		if ( tinv_get_option( 'references', 'cart' ) ) {
			add_filter( 'woocommerce_after_cart_item_name', array( $this, 'reference_cart' ), 100, 2 );
		}
		if ( tinv_get_option( 'references', 'checkout' ) ) {
			add_filter( 'woocommerce_checkout_cart_item_quantity', array( $this, 'reference_checkout' ), 100, 3 );
		}

		if ( tinv_get_option( 'references', 'order' ) ) {
			add_action( 'woocommerce_order_item_meta_start', array( $this, 'reference_order_item' ), 100, 3 );
			add_action( 'woocommerce_before_order_itemmeta', array( $this, 'reference_order_item' ), 100, 3 );
		}

		add_action( 'wp_logout', array( $this, 'reset_cookie' ) );
		add_action( 'wp_login', array( $this, 'reset_cookie' ) );
	}

	/**
	 * Reset cookies shaerkey on logout
	 * @return void
	 */
	function reset_cookie() {
		wc_setcookie( 'tinv_wishlistkey', 0, time() - HOUR_IN_SECONDS );
		unset( $_COOKIE['tinv_wishlistkey'] );
		wc_setcookie( 'tinvwl_wishlists_data_counter', 0, time() - HOUR_IN_SECONDS );
		unset( $_COOKIE['tinvwl_wishlists_data_counter'] );
		wc_setcookie( 'tinvwl_update_data', 1, time() + HOUR_IN_SECONDS );
	}

	/**
	 * Output wishlist reference
	 *
	 * @param array $product_from_wishlist product added from wishlist data
	 *
	 * @return string
	 */
	public function output_reference( $product_from_wishlist ) {

		if ( is_array( $product_from_wishlist ) ) {
			reset( $product_from_wishlist );
			$share_key = key( $product_from_wishlist );

			$wl       = new TInvWL_Wishlist();
			$wishlist = $wl->get_by_share_key( $share_key );
			if ( $wishlist ) {

				$prefix = tinv_get_option( 'references', 'label' );
				$prefix = $prefix ? $prefix . ' ' : '';

				return apply_filters( 'tinvwl_wishlist_reference_string', '<p>' . $prefix . '<a href="' . tinv_url_wishlist( $wishlist['share_key'] ) . '">' . $wishlist['title'] . '</a></p>', $wishlist );
			}
		}
	}

	/**
	 *  Output to cart item meta.
	 *
	 * @param array $cart_item
	 * @param string $cart_item_key
	 */
	public function reference_cart( $cart_item = array(), $cart_item_key = "" ) {
		$product_from_wishlist = TInvWL_Public_Cart::get_item_data( $cart_item_key );
		if ( $product_from_wishlist ) {
			echo $this->output_reference( $product_from_wishlist );
		}
	}

	/**
	 *  Output to checkout item.
	 *
	 * @param $string
	 * @param array $cart_item
	 * @param string $cart_item_key
	 *
	 * @return string
	 */
	public function reference_checkout( $string, $cart_item = array(), $cart_item_key = "" ) {
		$product_from_wishlist = TInvWL_Public_Cart::get_item_data( $cart_item_key );
		if ( $product_from_wishlist ) {
			$string .= $this->output_reference( $product_from_wishlist );
		}

		return $string;
	}

	/**
	 * Output to order item.
	 *
	 * @param $item_id
	 * @param $item
	 * @param $product
	 */
	public function reference_order_item( $item_id, $item, $product ) {
		$product_from_wishlist = TInvWL_Public_Cart::get_order_item_meta( $item, '_tinvwl_wishlist_cart' );
		if ( $product_from_wishlist ) {
			echo $this->output_reference( $product_from_wishlist );
		}
	}


	/**
	 * Load function
	 */
	function load_function() {
	}
}
