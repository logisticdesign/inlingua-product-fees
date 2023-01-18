<?php
/**
 * Plugin Name: Inlingua Product Fees
 * Version: 0.0.1
 *
 * Text Domain: inlingua-product-fees
 * Domain Path: /languages/
 *
 * Requires at least: 4.5
 * Tested up to: 4.9
 * WC tested up to: 3.3
 * WC requires at least: 3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action('plugins_loaded', 'wc_inligua_fees_load_after_plugins_loaded');

function wc_inligua_fees_load_after_plugins_loaded() {
	if ( ! class_exists('Inlingua_Product_Fees') && class_exists('WooCommerce')) {
		require_once('classes/class-inlingua-product-fees.php');
		require_once('helpers.php');

		new InliguaProductFees;
	}
}
