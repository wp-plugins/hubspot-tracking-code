<?php

if ( !defined('HUBSPOT_TRACKING_CODE_PLUGIN_VERSION') ) 
{
    header('HTTP/1.0 403 Forbidden');
    die;
}

//=============================================
// Define Constants
//=============================================

if ( !defined('HUBSPOT_TRACKING_CODE_ADMIN_PATH') )
    define('HUBSPOT_TRACKING_CODE_ADMIN_PATH', untrailingslashit(__FILE__));

//=============================================
// Include Needed Files
//=============================================

include_once(ABSPATH . 'wp-admin/includes/plugin.php');

//=============================================
// HubSpotTrackingCodeAdmin Class
//=============================================
class HubSpotTrackingCodeAdmin 
{
    /**
     * Class constructor
     */
    function __construct ()
    {
        //=============================================
        // Hooks & Filters
        //=============================================

        $options = get_option('hs_settings');

        // If the plugin version matches the latest version escape the update function
        if ( $options['hs_version'] != HUBSPOT_TRACKING_CODE_PLUGIN_VERSION )
            self::hubspot_tracking_code_update_check();
        
        add_action('admin_menu', array(&$this, 'hubspot_add_menu_items'));
        add_action('admin_init', array(&$this, 'hubspot_build_settings_page'));
        add_filter('plugin_action_links_' . HUBSPOT_TRACKING_CODE_PLUGIN_SLUG . '/hubspot-tracking-code.php', array($this, 'hubspot_plugin_settings_link'));
    }

    function hubspot_tracking_code_update_check ()
    {
        $options = get_option('hs_settings');

        // Set the plugin version
        hubspot_tracking_code_update_option('hs_settings', 'hs_version', HUBSPOT_TRACKING_CODE_PLUGIN_VERSION);
    }
    
    //=============================================
    // Menus
    //=============================================

    function hubspot_add_menu_items () 
    {
    	add_submenu_page('options-general.php', 'HubSpot Settings', 'HubSpot Settings', 'edit_posts', basename(__FILE__), array($this, 'hubspot_plugin_options'));
    }


    //=============================================
    // Settings Page
    //=============================================

    /**
     * Adds setting link for HubSpot to plugins management page 
     *
     * @param   array $links
     * @return  array
     */
    function hubspot_plugin_settings_link ( $links )
    {
        $url = get_admin_url() . 'options-general.php?page=hubspot-tracking-code-admin.php';
        $settings_link = '<a href="' . $url . '">Settings</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Creates settings options
     */
    function hubspot_build_settings_page ()
    {   
        global $pagenow;
        $options = get_option('hs_settings');

        register_setting(
            'hubspot_settings_options',
            'hs_settings',
            array($this, 'sanitize')
        );
        
        add_settings_section(
            'hubspot_settings_section',
            '',
            array($this, 'hs_settings_section_heading'),
            HUBSPOT_TRACKING_CODE_ADMIN_PATH
        );
        
        add_settings_field(
            'hs_portal',
            'Hub ID',
            array($this, 'hs_portal_callback'),
            HUBSPOT_TRACKING_CODE_ADMIN_PATH,
            'hubspot_settings_section'
        );
    }

    function hs_settings_section_heading ( )
    {
        $this->print_hidden_settings_fields();
    }

    function print_hidden_settings_fields ()
    {
         // Hacky solution to solve the Settings API overwriting the default values
        $options = get_option('hs_settings');

        $hs_installed = ( isset($options['hs_installed']) ? $options['hs_installed'] : 1 );
        $hs_version   = ( isset($options['hs_version']) ? $options['hs_version'] : HUBSPOT_TRACKING_CODE_PLUGIN_VERSION );

        printf(
            '<input id="hs_installed" type="hidden" name="hs_settings[hs_installed]" value="%d"/>',
            $hs_installed
        );

        printf(
            '<input id="hs_version" type="hidden" name="hs_settings[hs_version]" value="%s"/>',
            $hs_version
        );
    }

    /**
     * Creates settings page
     */
    function hubspot_plugin_options ()
    {
        ?>
        <div class="wrap">
        	<div class="dashboard-widgets-wrap">
	            <h2>HubSpot Tracking Code Settings</h2>
                <form method="POST" action="options.php">
                	<div id="dashboard-widgets" class="metabox-holder">
	                	<div class="postbox-container" style="width:60%;">	
	                		<div class="meta-box-sortables ui-sortable">
						        <div class="postbox">
						        	<h3 class="hndle"><span>Settings</span></h3>
						        	<div class="inside">
						        		Enter your Hub ID below to track your WordPress site in HubSpot's analytics system.
						        		<?php 
					                        settings_fields('hubspot_settings_options');
					                        do_settings_sections(HUBSPOT_TRACKING_CODE_ADMIN_PATH);
					                    ?>
						        	</div>
						        	
						        </div>
						    </div>
							<?php submit_button('Save Settings'); ?>
			            </div>

			            <div class="postbox-container" style="width:40%;">
			            	<div class="meta-box-sortables ui-sortable">	
						        <div class="postbox">
						        <h3 class="hndle"><span>Where is my HubSpot Hub ID?</span></h3>
						        	<div class="inside">
										<p><b>I'm setting up HubSpot for myself</b><br><a target='_blank' href='https://app.hubspot.com/'>Log in to HubSpot</a>. Your Hub ID is in the upper right corner of the screen.</p>
										<img style="max-width: 100%;" src="http://cdn2.hubspot.net/hubfs/250707/CRM_Knowledge/Sidekick/HubID.jpg?t=1437426192644"/>
										<p><b>I'm setting up HubSpot for someone else</b><br>If you received a "HubSpot Tracking Code Instructions" email, this contains the Hub ID.</p>
										<p><b>I'm interested in trying HubSpot</b><br> <a target='_blank' href='http://offers.hubspot.com/free-trial'>Sign up for a free 30-day trial</a> to get your Hub ID assigned.</a></p>
						        	</div>
						        </div>
						    </div>
					    </div>
			        </div>
                </form>
	        </div>
        </div>
        <?php
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize ( $input )
    {
        $new_input = array();

        $options = get_option('hs_settings');

        if ( isset($input['hs_portal']) )
            $new_input['hs_portal'] = $input['hs_portal'];

        if ( isset($input['hs_installed']) )
            $new_input['hs_installed'] = $input['hs_installed'];

        if ( isset($input['hs_version']) )
            $new_input['hs_version'] = $input['hs_version'];

        return $new_input;
    }

    /**
     * Prints Hub ID input for settings page
     */
    function hs_portal_callback ()
    {
        $options = get_option('hs_settings');
        $hs_portal  = ( isset($options['hs_portal']) && $options['hs_portal'] ? $options['hs_portal'] : '' );
     
        printf(
            '<input id="hs_portal" type="text" id="title" name="hs_settings[hs_portal]" style="width: 400px;" value="%s"/>',
            $hs_portal
        );

        //echo '<p><a href="http://help.hubspot.com/articles/KCS_Article/Account/Where-can-I-find-my-HUB-ID" target="_blank">Where can I find my HUB ID?</a></p>';
    }
}

?>