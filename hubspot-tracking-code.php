<?php
/*
Plugin Name: HubSpot Tracking Code for WordPress
Plugin URI: http://hubspot.com
Description: HubSpot's WordPress plugin allows existing HubSpot customers and trial users to install the HubSpot tracking code on their existing WordPress blogs and websites.
Version: 1.0.0
Author: HubSpotDev
Author URI: http://www.hubspot.com/integrations/wordpress
License: GPL2
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 

//=============================================
// Define Constants
//=============================================

if ( !defined('HUBSPOT_TRACKING_CODE_PATH') )
    define('HUBSPOT_TRACKING_CODE_PATH', untrailingslashit(plugins_url('', __FILE__ )));

if ( !defined('HUBSPOT_TRACKING_CODE_PLUGIN_DIR') )
	define('HUBSPOT_TRACKING_CODE_PLUGIN_DIR', untrailingslashit(dirname( __FILE__ )));

if ( !defined('HUBSPOT_TRACKING_CODE_PLUGIN_SLUG') )
	define('HUBSPOT_TRACKING_CODE_PLUGIN_SLUG', basename(dirname(__FILE__)));

if ( !defined('HUBSPOT_TRACKING_CODE_PLUGIN_VERSION') )
	define('HUBSPOT_TRACKING_CODE_PLUGIN_VERSION', '1.0.0');

//=============================================
// Include Needed Files
//=============================================

require_once(HUBSPOT_TRACKING_CODE_PLUGIN_DIR . '/inc/hubspot-tracking-code-functions.php');
require_once(HUBSPOT_TRACKING_CODE_PLUGIN_DIR . '/inc/class-hubspot-tracking-code.php');
require_once(HUBSPOT_TRACKING_CODE_PLUGIN_DIR . '/inc/class-hubspot-tracking-code-analytics.php');
require_once(HUBSPOT_TRACKING_CODE_PLUGIN_DIR . '/admin/hubspot-tracking-code-admin.php');

//=============================================
// Hooks & Filters
//=============================================

/**
 * Activate the plugin
 */
function hubspot_tracking_code_activate ( $network_wide )
{
	// Check activation on entire network or one blog
	if ( is_multisite() && $network_wide ) 
	{ 
		global $wpdb;
 
		// Get this so we can switch back to it later
		$current_blog = $wpdb->blogid;
 
		// Get all blogs in the network and activate plugin on each one
		$q = "SELECT blog_id FROM $wpdb->blogs";
		$blog_ids = $wpdb->get_col($q);
		foreach ( $blog_ids as $blog_id ) 
		{
			switch_to_blog($blog_id);
			hubspot_tracking_code_setup_plugin();
		}
 
		// Switch back to the current blog
		switch_to_blog($current_blog);
	}
	else
	{
		hubspot_tracking_code_setup_plugin();
	}
}

/**
 * Check Super Simple Landing Pages installation and register custom post type
 */
function hubspot_tracking_code_setup_plugin ( )
{
	$options = get_option('hs_settings');

	if ( ! isset($options['hs_installed']) || $options['hs_installed'] != "on" || (!is_array($options)) )
	{
		$opt = array(
			'hs_installed'	=> "on",
			'hs_version'	=> HUBSPOT_TRACKING_CODE_PLUGIN_VERSION
		);

		// this is a hack because multisite doesn't recognize local options using either update_option or update_site_option...
		if ( is_multisite() )
		{
			global $wpdb;

			$multisite_prefix = ( is_multisite() ? $wpdb->prefix : '' );
			$q = $wpdb->prepare("
				INSERT INTO " . $multisite_prefix . "options 
			        ( option_name, option_value ) 
			    VALUES ('hs_settings', %s)", serialize($opt));
			$wpdb->query($q);
		}
		else
			update_option('hs_settings', $opt);
	}
}

function hubspot_tracking_code_activate_on_new_blog ( $blog_id, $user_id, $domain, $path, $site_id, $meta )
{
	global $wpdb;

	if ( is_plugin_active_for_network('hubspot-tracking-code/hubspot-tracking-code.php') )
	{
		$current_blog = $wpdb->blogid;
		switch_to_blog($blog_id);
		hubspot_tracking_code_setup_plugin();
		switch_to_blog($current_blog);
	}
}

/**
 * Checks the stored database version against the current data version + updates if needed
 */
function hubspot_tracking_code_init ()
{
	if ( is_plugin_active('hubspot/hubspot.php') ) 
	{
		remove_action( 'plugins_loaded', 'hubspot_tracking_code_init' );
     	deactivate_plugins(plugin_basename( __FILE__ ));

		add_action( 'admin_notices', 'deactivate_hubspot_tracking_code_notice' );
	    return;
	}

    $hubspot_wp = new HubSpotTrackingCode();
}

add_action( 'plugins_loaded', 'hubspot_tracking_code_init', 14 );

if ( is_admin() ) 
{
	// Activate + install Super Simple Landing Pages
	register_activation_hook( __FILE__, 'hubspot_tracking_code_activate');

	// Activate on newly created wpmu blog
	add_action('wpmu_new_blog', 'hubspot_tracking_code_activate_on_new_blog', 10, 6);
}

function deactivate_hubspot_tracking_code_notice () 
{
    ?>
    <div id="message" class="error">
        <?php _e( 
        	'<p><h3>HubSpot Tracking Code plugin wasn\'t activated because your HubSpot for WordPress plugin is still activated...</h3></p>' . 
        		'<p>HubSpot Tracking Code and HubSpot for WordPress are like two rival siblings - they don\'t play nice together, but don\'t panic - it\'s an easy fix. Deactivate <b><i>HubSpot for WordPress</i></b> and then try activating <b><i>HubSpot Tracking Code for WordPress</i></b> again, and everything should work fine.</p>' .
        	'<p>By the way - make sure you replace all your form and CTA shortcodes with <a href="http://help.hubspot.com/articles/KCS_Article/Integrations/How-to-switch-from-the-HubSpot-for-Wordpress-plugin-to-the-HubSpot-Tracking-code-for-Wordpress-plugin" target="_blank">HubSpot embed codes</a></p>',
        	'my-text-domain'
        ); ?>
    </div>
    <?php
}

?>