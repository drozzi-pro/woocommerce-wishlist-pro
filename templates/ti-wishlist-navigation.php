<?php
/**
 * The Template for displaying wishlist navigation this plugin.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/ti-wishlist-navigation.php.
 *
 * @version             2.2.0
 * @package           TInvWishlist\Admin\Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div class="tinv-wishlist-clear"></div>
<ul class="navigation-button tinv-wishlist-clear">
	<?php if ( is_user_logged_in() && tinv_get_option( 'general', 'multi' ) && ( tinv_get_option( 'page', 'manage' ) || tinv_get_option( 'general', 'my_account_endpoint' ) ) ) { ?>
		<li><?php echo do_shortcode( '[ti_wishlists_create show="button"]' ); ?></li>
		<li>
			<a href="<?php echo esc_url( tinv_url_manage_wishlists() ); ?>"
			   class="<?php echo esc_attr( ( 'button' === $button_type ) ? 'button' : '' ) ?> <?php echo esc_attr( $button_icon ? '' : 'tinwl-no-icon' ); ?>"><?php if ( $button_icon ) { ?>
					<i class="ftinvwl ftinvwl-wrench"></i><?php } ?><span
					class="tinvwl-txt"><?php esc_html_e( 'My Wishlists', 'ti-woocommerce-wishlist-premium' ) ?></span></a>
		</li>
	<?php } ?>
	<?php if ( is_user_logged_in() && TInvWL_Public_Notifications::show_option() ) { ?>
		<li><?php echo do_shortcode( '[ti_wishlists_notifications]' ); ?></li>
	<?php } ?>
	<?php if ( tinv_get_option( 'navigation', 'public' ) && tinv_get_option( 'page', 'public' ) && ! tinv_get_option( 'general', 'my_account_endpoint' ) ) { ?>
		<li>
			<a href="<?php echo esc_url( get_permalink( apply_filters( 'wpml_object_id', tinv_get_option( 'page', 'public' ), 'page', true ) ) ); ?>"
			   class="<?php echo esc_attr( ( 'button' === $button_type ) ? 'button' : '' ) ?> <?php echo esc_attr( $button_icon ? '' : 'tinwl-no-icon' ); ?>"><?php if ( $button_icon ) { ?>
					<i class="ftinvwl ftinvwl-bars"></i><?php } ?><span
					class="tinvwl-txt"><?php esc_html_e( 'All Wishlists', 'ti-woocommerce-wishlist-premium' ) ?></span></a>
		</li>
	<?php } ?>
	<?php if ( tinv_get_option( 'navigation', 'searchp' ) && tinv_get_option( 'page', 'searchp' ) && ! tinv_get_option( 'general', 'my_account_endpoint' ) ) { ?>
		<li>
			<a href="<?php echo esc_url( get_permalink( apply_filters( 'wpml_object_id', tinv_get_option( 'page', 'searchp' ), 'page', true ) ) ); ?>"
			   class="<?php echo esc_attr( ( 'button' === $button_type ) ? 'button' : '' ) ?> <?php echo esc_attr( $button_icon ? '' : 'tinwl-no-icon' ); ?>"><?php if ( $button_icon ) { ?>
					<i class="ftinvwl ftinvwl-search"></i><?php } ?><span
					class="tinvwl-txt"><?php esc_html_e( 'Search', 'ti-woocommerce-wishlist-premium' ) ?></span></a>
		</li>
	<?php } ?>
	<?php if ( tinv_get_option( 'navigation', 'continue' ) ) { ?>
		<li>
			<a href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>"
			   class="<?php echo esc_attr( ( 'button' === $button_type ) ? 'button' : '' ); ?> <?php echo esc_attr( $button_icon ? '' : 'tinwl-no-icon' ); ?>"><?php if ( $button_icon ) { ?>
					<i class="ftinvwl ftinvwl-chevron-left"></i><?php } ?><span
					class="tinvwl-txt"><?php esc_html_e( 'Continue Shopping', 'ti-woocommerce-wishlist-premium' ) ?></span></a>
		</li>
	<?php } ?>
</ul>
