<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Sliced Invoices & Gravity Forms
 * Plugin URI:        https://slicedinvoices.com/extensions/gravity-forms
 * Description:       Create forms that allow users to submit a quote or estimate request. Requirements: The Sliced Invoices Plugin and Gravity Forms
 * Version:           1.04
 * Author:            Sliced Invoices
 * Author URI:        https://slicedinvoices.com/
 * Text Domain:       sliced-invoices-gravity-forms
 * Domain Path:       /languages
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'gform_loaded', array( 'Sliced_Invoices_GF_Bootstrap', 'load' ), 5 );

class Sliced_Invoices_GF_Bootstrap {

	public static function load() {
		if ( ! method_exists( 'GFForms', 'include_feed_addon_framework' ) ) {
			return;
		}

		require_once( 'class-sliced-invoices-gf.php' );
		GFAddOn::register( 'Sliced_Invoices_GF' );
	}

}