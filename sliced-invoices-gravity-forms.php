<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Sliced Invoices & Gravity Forms
 * Plugin URI:        https://slicedinvoices.com/extensions/gravity-forms
 * Description:       Create forms that allow users to submit a quote or estimate request. Requirements: The Sliced Invoices Plugin and Gravity Forms
 * Version:           1.12.5
 * Author:            Sliced Invoices
 * Author URI:        https://slicedinvoices.com/
 * Text Domain:       sliced-invoices-gravity-forms
 * Domain Path:       /languages
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/*
 * Requirements check
 */
add_action( 'init', 'sliced_gf_validate_settings' );

function sliced_gf_requirements_not_met_notice_sliced() {
	echo '<div id="message" class="error">';
	echo '<p>' . sprintf( __( 'Sliced Invoices & Gravity Forms extension cannot find the required <a href="%s">Sliced Invoices plugin</a>. Please make sure the core Sliced Invoices plugin is <a href="%s">installed and activated</a>.', 'sliced-invoices-gravity-forms' ), 'https://wordpress.org/plugins/sliced-invoices/', admin_url( 'plugins.php' ) ) . '</p>';
	echo '</div>';
}

function sliced_gf_requirements_not_met_notice_gf() {
	echo '<div id="message" class="error">';
	echo '<p>' . sprintf( __( 'Sliced Invoices & Gravity Forms extension cannot find the required <a href="%s">Gravity Forms plugin</a>. Please make sure the Gravity Forms plugin is <a href="%s">installed and activated</a>.', 'sliced-invoices-gravity-forms' ), 'https://www.gravityforms.com/', admin_url( 'plugins.php' ) ) . '</p>';
	echo '</div>';
}

function sliced_gf_validate_settings() {

	if ( ! class_exists( 'Sliced_Invoices' ) ) {
		// Add a dashboard notice.
		add_action( 'all_admin_notices', 'sliced_gf_requirements_not_met_notice_sliced' );
	}
	
	if ( ! class_exists( 'GFForms' ) ) {
		// Add a dashboard notice.
		add_action( 'all_admin_notices', 'sliced_gf_requirements_not_met_notice_gf' );
	}
}
	

/*
 * Make it so...
 */
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